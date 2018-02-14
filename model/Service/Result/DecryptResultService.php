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
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoResultServer\models\classes\implementation\ResultServerService;
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoResultServer\models\Entity\TestVariableStorable;

class DecryptResultService extends ConfigurableService implements DecryptResult
{
    use LoggerAwareTrait;

    const OPTION_ENCRYPTION_SERVICE = EncryptResultService::OPTION_ENCRYPTION_SERVICE;

    const OPTION_PERSISTENCE =  EncryptResultService::OPTION_PERSISTENCE;

    const PREFIX_DELIVERY_EXECUTION = EncryptResultService::PREFIX_DELIVERY_EXECUTION;

    const PREFIX_DELIVERY_RESULTS_ITEMS = EncryptResultService::PREFIX_DELIVERY_RESULTS_ITEMS;

    const PREFIX_DELIVERY_RESULTS = EncryptResultService::PREFIX_DELIVERY_RESULTS;

    const PREFIX_TEST_TAKER = EncryptResultService::PREFIX_TEST_TAKER;

    /**
     * @inheritdoc
     */
    public function decrypt($deliveryIdentifier)
    {
        $resultStorage = $this->getResultStorage($deliveryIdentifier);
        $results = $this->getResults($deliveryIdentifier);

        foreach ($results as $resultId){
            $relatedTestTaker = $this->getRelatedTestTaker($resultId);
            $relatedDelivery  = $this->getRelatedDelivery($resultId);
            $itemsTestsRefs   = $this->getItemsTestsRefs($resultId);

            foreach ($itemsTestsRefs as $ref) {
                $resultRow = $this->getResultRow($ref);
                if ($resultRow instanceof ItemVariableStorable) {
                    $resultStorage->storeItemVariable(
                        $resultRow->getDeliveryResultIdentifier(),
                        $resultRow->getTestIdentifier(),
                        $resultRow->getItemIdentifier(),
                        $resultRow->getVariable(),
                        $resultRow->getCallItemId()
                    );
                    $this->getPersistence()->del($ref);

                } else if ($resultRow instanceof TestVariableStorable) {
                    $resultStorage->storeTestVariable(
                        $resultRow->getDeliveryResultIdentifier(),
                        $resultRow->getTestIdentifier(),
                        $resultRow->getVariable(),
                        $resultRow->getCallTestId()
                    );
                    $this->getPersistence()->del($ref);
                }
            }

            $resultStorage->storeRelatedDelivery(
                $relatedDelivery['deliveryResultIdentifier'],
                $relatedDelivery['deliveryIdentifier']
            );
            $this->deleteRelatedDelivery($resultId);

            $resultStorage->storeRelatedTestTaker(
                $relatedTestTaker['deliveryResultIdentifier'],
                $relatedTestTaker['testTakerIdentifier']
            );

            $this->deleteRelatedTestTaker($resultId);
            $this->deleteItemsTestsRefs($resultId);
            $this->deleteResult($deliveryIdentifier, $resultId);
        }
    }

    /**
     * @return EncryptionServiceInterface
     */
    protected function getEncryptionService()
    {
        /** @var EncryptionServiceInterface $service */
        $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_SERVICE));

        return $service;
    }

    /**
     * @throws \Exception
     * @return common_persistence_KvDriver
     */
    protected function getPersistence()
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        $persistence = $this->getServiceManager()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);

        if (!$persistence instanceof common_persistence_KeyValuePersistence) {
            throw new \Exception('Only common_persistence_KeyValuePersistence supported');
        }

        return $persistence;
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
        $values = $this->getPersistence()->get(static::PREFIX_DELIVERY_RESULTS_ITEMS . $resultId);

        return json_decode($values, true);
    }

    /**
     * @param $resultId
     * @return mixed
     * @throws \Exception
     */
    protected function deleteItemsTestsRefs($resultId)
    {
        return $this->getPersistence()->del(static::PREFIX_DELIVERY_RESULTS_ITEMS . $resultId);
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
        $results = $this->getPersistence()->get(static::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier);
        return json_decode($results, true);
    }

    /**
     * @param $deliveryIdentifier
     * @param $resultId
     * @return bool
     * @throws \Exception
     */
    protected function deleteResult($deliveryIdentifier, $resultId)
    {
        $results = $this->getPersistence()->get(static::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier);
        $results = json_decode($results, true);;

        if (($key = array_search($resultId, $results)) !== false) {
            unset($results[$key]);
            return $this->getPersistence()->set(static::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier, json_encode($results));
        }

        return false;
    }
}