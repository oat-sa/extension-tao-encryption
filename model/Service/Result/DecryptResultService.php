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
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoEncryption\Model\Exception\EmptyContentException;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoResultServer\models\Entity\TestVariableStorable;
use \common_report_Report as Report;

class DecryptResultService extends ConfigurableService implements DecryptResult
{
    use LoggerAwareTrait;

    const OPTION_ENCRYPTION_SERVICE = EncryptResultService::OPTION_ENCRYPTION_SERVICE;

    const OPTION_PERSISTENCE =  EncryptResultService::OPTION_PERSISTENCE;

    const PREFIX_DELIVERY_EXECUTION = EncryptResultService::PREFIX_DELIVERY_EXECUTION;

    const PREFIX_TEST_TAKER = EncryptResultService::PREFIX_TEST_TAKER;

    /** @var common_persistence_KvDriver */
    private $persistence;

    /** @var EncryptionServiceInterface*/
    private $encryptionService;

    /** @var DeliveryResultsModel */
    private $deliveryResultsModel;

    /** @var DeliveryResultVarsRefsModel */
    public $deliveryResultVarsRefs;

    /**
     * @inheritdoc
     */
    public function decrypt($deliveryIdentifier)
    {
        /** @var SyncEncryptedResultService $resultService */
        $resultService    = $this->getServiceLocator()->get(SyncEncryptedResultService::SERVICE_ID);
        $report           = Report::createInfo('Decrypt Results for delivery id: '. $deliveryIdentifier);
        $resultStorage    = $this->getResultStorage($deliveryIdentifier);
        $results          = $this->getResults($deliveryIdentifier);
        $resultsDecrypted = [];

        foreach ($results as $resultId){
            try{
                $relatedTestTaker = $this->getRelatedTestTaker($resultId);
                $relatedDelivery  = $this->getRelatedDelivery($resultId);
                $itemsTestsRefs   = $this->getItemsTestsRefs($resultId);

                if (!isset($relatedDelivery['deliveryResultIdentifier'])
                    || !isset($relatedDelivery['deliveryIdentifier'])
                    || !isset($relatedTestTaker['testTakerIdentifier']))
                {
                    continue;
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
                    $resultRow = $this->getResultRow($ref);

                    if ($resultRow instanceof ItemVariableStorable) {
                        $resultStorage->storeItemVariable(
                            $deliveryResultIdentifier,
                            $resultRow->getTestIdentifier(),
                            $resultRow->getItemIdentifier(),
                            $resultRow->getVariable(),
                            $resultRow->getCallItemId() . '|' .$deliveryResultIdentifier
                        );

                    } else if ($resultRow instanceof TestVariableStorable) {
                        $resultStorage->storeTestVariable(
                            $deliveryResultIdentifier,
                            $resultRow->getTestIdentifier(),
                            $resultRow->getVariable(),
                            $deliveryResultIdentifier
                        );
                    }
                }

                $this->deleteRelatedDelivery($resultId);
                $this->deleteRelatedTestTaker($resultId);
                $this->deleteItemsTestsRefs($resultId);
                $resultsDecrypted[] = $resultId;

                $deliveryExecution = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID)->getDeliveryExecution($resultId);
                $resultService->triggerResultEvent($deliveryExecution);

                $report->add(Report::createSuccess('Result decrypted with success:'. $resultId));
            } catch (EmptyContentException $exception) {
                $resultsDecrypted[] = $resultId;
                $report->add(Report::createInfo('Result decrypted FAILED:'. $resultId . ' '. $exception->getMessage()));
            } catch (\Exception $exception) {
                $report->add(Report::createFailure('Result decrypted FAILED:'. $resultId . ' '. $exception->getMessage()));
            }
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
}
