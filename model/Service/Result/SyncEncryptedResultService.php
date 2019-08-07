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
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoEncryption\Service\Mapper\MapperClientUserIdToCentralUserIdInterface;
use oat\taoEncryption\Task\DecryptResultTask;
use oat\taoSync\model\ResultService;
use Psr\Log\LogLevel;

class SyncEncryptedResultService extends ResultService
{
    use OntologyAwareTrait;

    const OPTION_PERSISTENCE = 'persistence';

    const OPTION_ENCRYPTION_SERVICE = 'asymmetricEncryptionService';

    const OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL = 'userIdClientToUserIdCentral';

    /** @var  common_persistence_KvDriver*/
    private $persistence;

    /** @var  EncryptionServiceInterface*/
    private $encryptionService;

    /** @var DeliveryResultsModel */
    private $deliveryResultsModel;

    /** @var DeliveryResultVarsRefsModel */
    public $deliveryResultVarsRefs;

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function importDeliveryResults(array $results, array $params = [])
    {
        $this->initImport($params);
        $importAcknowledgment    = [];
        $resultsOfDeliveryMapper = [];
        $mapper = $this->getUserIdClientToUserIdCentralMapper();

        foreach ($results as $resultId => $result) {
            $success = true;

            try {
                $this->checkResultFormat($result);

                $deliveryId = $result['deliveryId'];
                $details = $result['details'];
                $variables = $result['variables'];

                $delivery = $this->getResource($deliveryId);

                $ltiCentralUserId = $mapper->getCentralUserId($details['test-taker']);
                if ($ltiCentralUserId !== false) {
                    $details['test-taker'] = $ltiCentralUserId;
                }

                $testtaker = $this->getResource($details['test-taker']);

                $deliveryExecution = $this->spawnDeliveryExecution($resultId, $delivery, $testtaker);
                $deliveryExecution = $this->updateDeliveryExecution($details, $deliveryExecution);

                $deliveryExecutionId = $deliveryExecution->getIdentifier();
                $resultsOfDeliveryMapper[$deliveryId][] = $deliveryExecutionId;

                $this->getPersistence()->set(
                    EncryptResultService::PREFIX_TEST_TAKER . $deliveryExecutionId,
                    $this->getEncryptionService()->encrypt(json_encode([
                        "deliveryResultIdentifier" => $deliveryExecutionId,
                        "testTakerIdentifier" => $testtaker->getUri()
                    ]))
                );

                $this->getPersistence()->set(
                    EncryptResultService::PREFIX_DELIVERY_EXECUTION . $deliveryExecutionId,
                    $this->getEncryptionService()->encrypt(json_encode([
                        "deliveryResultIdentifier" => $deliveryExecutionId,
                        "deliveryIdentifier" => $delivery->getUri()
                    ]))
                );

                foreach ($variables as $ref => $variableRow) {
                    $this->getPersistence()->set($ref, $variableRow);

                    $mapping = $this->getDeliveryResultVarsRefsModel()->getResultsVariablesRefs($deliveryExecutionId);
                    if (!in_array($ref, $mapping)){
                        $mapping[] = $ref;
                        $this->getDeliveryResultVarsRefsModel()->setResultsVariablesRefs($deliveryExecutionId, $mapping);
                    }
                }

                $this->mapOfflineResultIdToOnlineResultId($resultId, $deliveryExecutionId);
            } catch (\Exception $e) {
                $success = false;
            }

            if (isset($deliveryId)) {
                $importAcknowledgment[$resultId] = [
                    'success' => (int) $success,
                    'deliveryId' => $deliveryId,
                ];
                $this->report->add(\common_report_Report::createInfo("Delivery execution {$resultId} successfully imported."));
            } else {
                $importAcknowledgment[$resultId] = [
                    'success' => (int) $success,
                ];
                $this->report->add(\common_report_Report::createFailure("Import failed for delivery execution {$resultId}."));
            }
        }

        foreach ($resultsOfDeliveryMapper as $deliveryId => $resultsIds){
            $oldResults = $this->getDeliveryResultsModel()->getResultsReferences($deliveryId);

            $resultsReferences = array_merge($oldResults, $resultsIds);

            $this->getDeliveryResultsModel()->setResultsReferences(
                $deliveryId,
                array_unique($resultsReferences)
            );

            foreach ($resultsIds as $resultId) {
                $this->dispatchDecryptTask($deliveryId, $resultId);
            }
        }

        $this->reportImportCompleted($importAcknowledgment);

        return $importAcknowledgment;
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
     * Delete a delivery execution from array:
     * `array (
     * 'delivery1 => de1,
     * 'delivery1 => de2,
     * 'delivery2 => de3,
     * )`
     *
     * @param array $successfullyExportedResults
     * @throws \common_exception_Error
     * @throws \Exception
     */
    protected function deleteSynchronizedResult(array $successfullyExportedResults)
    {
        $deliveriesResultsDeleted = [];

        foreach ($successfullyExportedResults as $deliveryExecutionId => $deliveryId) {

            $this->getPersistence()->del(DecryptResultService::PREFIX_DELIVERY_EXECUTION . $deliveryExecutionId);
            $this->getPersistence()->del(DecryptResultService::PREFIX_TEST_TAKER . $deliveryExecutionId);
            $this->getDeliveryResultVarsRefsModel()->deleteRefs($deliveryExecutionId);
        }

        foreach ($deliveriesResultsDeleted as $deliveryId){
            $this->getDeliveryResultsModel()->deleteResultsReference($deliveryId);
        }

        $this->report(count($successfullyExportedResults) . ' deleted.', LogLevel::INFO);
    }

    /**
     * @param $deliveryId
     * @param string $resultId
     */
    protected function dispatchDecryptTask($deliveryId, $resultId)
    {
        /** @var QueueDispatcher $queue */
        $queue = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);

        $decryptResultTask = new DecryptResultTask();
        $this->propagate($decryptResultTask);

        $queue->createTask($decryptResultTask, [
            'deliveryIdentifier' => $deliveryId,
            'deliveryResultId' => $resultId,
        ], 'Decrypt Results');
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
}