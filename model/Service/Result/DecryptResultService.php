<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoEncryption\Service\Result;

use common_persistence_KeyValuePersistence;
use common_persistence_KvDriver;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoEncryption\Model\Exception\EmptyContentException;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoEncryption\Service\Mapper\MapperClientUserIdToCentralUserIdInterface;
use oat\taoEncryption\Service\Mapper\TestSessionSyncMapper;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoResultServer\models\Entity\TestVariableStorable;
use \common_report_Report as Report;
use oat\taoSync\model\TestSession\SyncTestSessionServiceInterface;

class DecryptResultService extends ConfigurableService implements DecryptResult
{
    use LoggerAwareTrait;

    const OPTION_ENCRYPTION_SERVICE = EncryptResultService::OPTION_ENCRYPTION_SERVICE;

    const OPTION_PERSISTENCE =  EncryptResultService::OPTION_PERSISTENCE;

    const OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL = 'userIdClientToUserIdCentral';

    const OPTION_STORE_VARIABLE_SERVICE = 'storeVariableService';

    const PREFIX_DELIVERY_EXECUTION = EncryptResultService::PREFIX_DELIVERY_EXECUTION;

    const PREFIX_TEST_TAKER = EncryptResultService::PREFIX_TEST_TAKER;

    /** @var common_persistence_KvDriver */
    private $persistence;

    /** @var EncryptionServiceInterface*/
    private $encryptionService;

    /** @var DeliveryResultsModel */
    private $deliveryResultsModel;

    /** @var DeliveryResultVarsRefsModel */
    private $deliveryResultVarsRefs;

    /** @var TestSessionSyncMapper */
    private $testSessionSyncMapper;

    /** @var SyncTestSessionServiceInterface */
    private $syncTestSessionService;

    /**
     * @param $deliveryIdentifier
     * @param $resultId
     * @return Report
     * @throws \common_exception_Error
     */
    public function decryptByExecution($deliveryIdentifier, $resultId)
    {
        //touch session generate a undefined index notice.
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $resultsDecrypted = [];

        return $this->decryptByResult($deliveryIdentifier, $resultId, $resultsDecrypted);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function decrypt($deliveryIdentifier)
    {
        //touch session generate a undefined index notice.
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $report           = Report::createInfo('Decrypt Results for delivery id: '. $deliveryIdentifier);
        $results          = $this->getResults($deliveryIdentifier);
        $resultsDecrypted = [];

        foreach ($results as $resultId){
            $report->add($this->decryptByResult($deliveryIdentifier, $resultId, $resultsDecrypted));
        }

        if ($results === $resultsDecrypted){
            $report->add(Report::createSuccess('All results decrypted for delivery:'. $deliveryIdentifier));
        }

        $newResults = $this->getDeliveryResultsModel()->getResultsReferences($deliveryIdentifier);
        $remainingResults = array_diff($newResults, $resultsDecrypted);
        $this->setResultsReferences($deliveryIdentifier, $remainingResults);

        return $report;
    }

    /**
     * @param $deliveryIdentifier
     * @param $resultId
     * @param $resultsDecrypted
     * @return Report
     * @throws \common_exception_Error
     * @throws \Exception
     */
    protected function decryptByResult($deliveryIdentifier, $resultId, &$resultsDecrypted)
    {
        $report  = Report::createInfo('Decrypt Results for delivery execution id: '. $resultId);
        $resultStorage = $this->getResultStorage($deliveryIdentifier);
        $mapper        = $this->getUserIdClientToUserIdCentralMapper();
        $variableStoreService = $this->getStoreVariableService();

        try{
            $relatedTestTaker = $this->getRelatedTestTaker($resultId);
            $relatedDelivery  = $this->getRelatedDelivery($resultId);
            $itemsTestsRefs   = $this->getItemsTestsRefs($resultId);

            if (!isset($relatedDelivery['deliveryResultIdentifier'])
                || !isset($relatedDelivery['deliveryIdentifier'])
                || !isset($relatedTestTaker['testTakerIdentifier']))
            {
                return $report;
            }

            $ltiCentralUserId = $mapper->getCentralUserId($relatedTestTaker['testTakerIdentifier']);
            if ($ltiCentralUserId !== false) {
                $relatedTestTaker['testTakerIdentifier'] = $ltiCentralUserId;
            }

            $deliveryResultIdentifier = $relatedDelivery['deliveryResultIdentifier'];

            $resultStorage->storeRelatedDelivery(
                $deliveryResultIdentifier,
                $relatedDelivery['deliveryIdentifier']
            );

            $resultStorage->storeRelatedTestTaker(
                $deliveryResultIdentifier,
                $relatedTestTaker['testTakerIdentifier']
            );

            foreach ($itemsTestsRefs as $ref) {
                $variableStoreService->save(
                    $deliveryResultIdentifier,
                    $this->getResultRow($ref),
                    $resultStorage
                );
            }

            $this->deleteRelatedDelivery($resultId);
            $this->deleteRelatedTestTaker($resultId);
            $this->deleteItemsTestsRefs($resultId);

            $resultsDecrypted[] = $resultId;

            $this->postDecryptOfResult($deliveryResultIdentifier);
            $report->add(Report::createSuccess('Result decrypted with success:'. $resultId));
        } catch (EmptyContentException $exception) {
            $resultsDecrypted[] = $resultId;
            $report->add(Report::createInfo('Result decrypted FAILED:'. $resultId . ' '. $exception->getMessage()));
        } catch (\Exception $exception) {
            $report->add(Report::createFailure('Result decrypted FAILED:'. $resultId . ' '. $exception->getMessage()));
        }

        return $report;

    }
    /**
     * @return EncryptionServiceInterface
     */
    protected function getEncryptionService()
    {
        if (is_null($this->encryptionService)){
            /** @var EncryptionServiceInterface $service */
            $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_SERVICE));

            $this->encryptionService = $service;
        }

        return $this->encryptionService;
    }

    /**
     * @throws \Exception
     * @return common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)){
            $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
            $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);

            if (!$persistence instanceof common_persistence_KeyValuePersistence) {
                throw new \Exception('Only common_persistence_KeyValuePersistence supported');
            }

            $this->persistence = $persistence;
        }

        return $this->persistence;
    }

    /**
     * @param $resultId
     * @return mixed
     * @throws \Exception
     * @throws \oat\taoEncryption\Model\Exception\DecryptionFailedException
     */
    protected function getRelatedTestTaker($resultId)
    {
        $value = $this->getPersistence()->get(static::PREFIX_TEST_TAKER . $resultId);
        if (!$value){
            throw new EmptyContentException();
        }
        $value = $this->getEncryptionService()->decrypt($value);

        return json_decode($value, true);
    }

    /**
     * @param $resultId
     * @throws \Exception
     */
    protected function deleteRelatedTestTaker($resultId)
    {
        return $this->getPersistence()->del(static::PREFIX_TEST_TAKER . $resultId);
    }

    /**
     * @param $resultId
     * @return mixed
     * @throws \Exception
     * @throws \oat\taoEncryption\Model\Exception\DecryptionFailedException
     */
    protected function getRelatedDelivery($resultId)
    {
        $value = $this->getPersistence()->get(static::PREFIX_DELIVERY_EXECUTION . $resultId);
        if (!$value){
            throw new EmptyContentException();
        }
        $value = $this->getEncryptionService()->decrypt($value);

        return json_decode($value, true);
    }

    /**
     * @param $resultId
     * @return bool
     * @throws \Exception
     */
    protected function deleteRelatedDelivery($resultId)
    {
        return $this->getPersistence()->del(static::PREFIX_DELIVERY_EXECUTION . $resultId);
    }

    /**
     * @param $resultId
     * @return mixed
     * @throws \Exception
     */
    protected function getItemsTestsRefs($resultId)
    {
        return $this->getDeliveryResultVarsRefsModel()->getResultsVariablesRefs($resultId);
    }

    /**
     * @param $resultId
     * @return mixed
     * @throws \Exception
     */
    protected function deleteItemsTestsRefs($resultId)
    {
        return $this->getDeliveryResultVarsRefsModel()->deleteRefs($resultId);
    }

    /**
     * @param $deliveryIdentifier
     * @return \taoResultServer_models_classes_WritableResultStorage
     * @throws \common_exception_Error
     */
    protected function getResultStorage($deliveryIdentifier)
    {
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        return $resultService->getResultStorage($deliveryIdentifier);
    }

    /**
     * @param $ref
     * @return bool|DecryptResultService|ItemVariableStorable|TestVariableStorable
     * @throws DecryptionFailedException
     * @throws \Exception
     */
    protected function getResultRow($ref)
    {
        $value = (string)$this->getPersistence()->get($ref);
        if (!$value){
            throw new EmptyContentException();
        }
        $value = json_decode($this->getEncryptionService()->decrypt($value), true);

        if (isset($value['item'])) {
            return ItemVariableStorable::createFromArray($value);
        } else if (isset($value['test'])) {
            return TestVariableStorable::createFromArray($value);
        } else {
            return false;
        }
    }

    /**
     * @param $deliveryIdentifier
     * @return mixed
     * @throws \Exception
     */
    protected function getResults($deliveryIdentifier)
    {
        return $this->getDeliveryResultsModel()->getResultsReferences($deliveryIdentifier);
    }

    /**
     * @param $deliveryIdentifier
     * @param $resultId
     * @return bool
     * @throws \Exception
     */
    protected function deleteResultsReference($deliveryIdentifier)
    {
        return $this->getDeliveryResultsModel()->deleteResultsReference($deliveryIdentifier);
    }

    /**
     * @param $deliveryIdentifier
     * @param array $results
     * @return bool
     * @throws \Exception
     */
    protected function setResultsReferences($deliveryIdentifier, array $results)
    {
        return $this->getDeliveryResultsModel()->setResultsReferences($deliveryIdentifier, $results);
    }

    /**
     * @return DeliveryResultsModel
     * @throws \Exception
     */
    protected function getDeliveryResultsModel()
    {
        if (is_null($this->deliveryResultsModel)){
            $this->deliveryResultsModel = new DeliveryResultsModel($this->getPersistence());
        }

        return $this->deliveryResultsModel;
    }

    /**
     * @return DeliveryResultVarsRefsModel
     * @throws \Exception
     */
    protected function getDeliveryResultVarsRefsModel()
    {
        if (is_null($this->deliveryResultVarsRefs)){
            $this->deliveryResultVarsRefs = new DeliveryResultVarsRefsModel($this->getPersistence());
        }

        return $this->deliveryResultVarsRefs;
    }

    /**
     * @param string $deliveryResultIdentifier
     * @throws \Exception
     */
    protected function postDecryptOfResult($deliveryResultIdentifier)
    {
        $sessionSynced = (bool)$this->getTestSessionSyncMapper()->get($deliveryResultIdentifier);

        if ($sessionSynced) {
            $deliveryExecution = $this->getDeliveryExecution($deliveryResultIdentifier);

            $this->getSyncTestSessionService()->touchTestSession($deliveryExecution);
            $this->getTestSessionSyncMapper()->delete($deliveryResultIdentifier);
        }
    }

    /**
     * @param $deliveryResultIdentifier
     * @return DeliveryExecution
     */
    protected function getDeliveryExecution($deliveryResultIdentifier)
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID)
            ->getDeliveryExecution($deliveryResultIdentifier);
    }
    /**
     * @return array|TestSessionSyncMapper|object
     */
    private function getTestSessionSyncMapper()
    {
        if (is_null($this->testSessionSyncMapper)) {
            $this->testSessionSyncMapper = $this->getServiceLocator()->get(TestSessionSyncMapper::SERVICE_ID);
        }

        return $this->testSessionSyncMapper;
    }

    /**
     * @return array|SyncTestSessionServiceInterface|object
     */
    private function getSyncTestSessionService()
    {
        if (is_null($this->syncTestSessionService)) {
            $this->syncTestSessionService = $this->getServiceLocator()->get(SyncTestSessionServiceInterface::SERVICE_ID);
        }

        return $this->syncTestSessionService;
    }

    /**
     * @return MapperClientUserIdToCentralUserIdInterface
     * @throws \Exception
     */
    protected function getUserIdClientToUserIdCentralMapper()
    {
        /** @var MapperClientUserIdToCentralUserIdInterface $mapper */
        $mapper = $this->getServiceLocator()->get($this->getOption(static::OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL));

        if (!$mapper instanceof MapperClientUserIdToCentralUserIdInterface) {
            throw new \Exception('Mapper needs to be a MapperLtiClientUserIdToCentralUserIdInterface');
        }

        return $mapper;
    }

    /**
     * @return StoreVariableServiceInterface
     * @throws \Exception
     */
    protected function getStoreVariableService()
    {
        /** @var StoreVariableServiceInterface $service */
        $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_STORE_VARIABLE_SERVICE));

        if (!$service instanceof StoreVariableServiceInterface) {
            throw new \Exception('Store Service needs to be a StoreVariableServiceInterface');
        }

        return $service;
    }
}
