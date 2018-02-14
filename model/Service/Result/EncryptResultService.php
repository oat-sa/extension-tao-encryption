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
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoResultServer\models\Entity\TestVariableStorable;
use oat\taoResultServer\models\Entity\VariableStorable;
use taoResultServer_models_classes_Variable;

class EncryptResultService extends ConfigurableService implements EncryptResult
{
    use LoggerAwareTrait;

    const OPTION_ENCRYPTION_SERVICE = 'asymmetricEncryptionService';

    const OPTION_PERSISTENCE = 'persistence';

    const PREFIX_DELIVERY_EXECUTION = 'encryptResultsDeliveryExecution_';

    const PREFIX_DELIVERY_RESULTS = 'encryptDeliveryResults';

    const PREFIX_DELIVERY_RESULTS_ITEMS = 'encryptMappingResultsItems';

    const PREFIX_TEST_TAKER = 'encryptTestTakerResultsDelivery_';

    /**
     * @inheritdoc
     */
    public function encrypt($data)
    {
        return $this->getEncryptionService()->encrypt($data);
    }

    /***
     * @return string|void
     */
    public function spawnResult()
    {
        $this->logAlert(__CLASS__ . '::spawnResult not supported');
    }

    /**
     * @param string $deliveryResultIdentifier
     * @param string $testTakerIdentifier
     * @throws \Exception
     */
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        $this->getPersistence()->set(self::PREFIX_TEST_TAKER . $deliveryResultIdentifier, $this->encrypt(json_encode([
            "deliveryResultIdentifier" => $deliveryResultIdentifier,
            "testTakerIdentifier" => $testTakerIdentifier
        ])));
    }

    /**
     * @param string $deliveryResultIdentifier
     * @param string $deliveryIdentifier
     * @throws \Exception
     */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        $this->getPersistence()->set(self::PREFIX_DELIVERY_EXECUTION . $deliveryResultIdentifier,  $this->encrypt(json_encode([
            "deliveryResultIdentifier" => $deliveryResultIdentifier,
            "deliveryIdentifier" => $deliveryIdentifier
        ])));

        $results = (string) $this->getPersistence()->get(self::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier);
        $results = $results === '' ? [] : json_decode($results, true);
        $results[] = $deliveryResultIdentifier;

        $this->getPersistence()->set(self::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier, json_encode($results));
    }

    /**
     * @param string $deliveryResultIdentifier
     * @param string $test
     * @param string $item
     * @param taoResultServer_models_classes_Variable $itemVariable
     * @param string $callIdItem
     * @return bool
     * @throws \Exception
     */
    public function storeItemVariable(
        $deliveryResultIdentifier,
        $test,
        $item,
        taoResultServer_models_classes_Variable $itemVariable,
        $callIdItem
    )
    {
        $keyStore = $this->buildStoreKey($deliveryResultIdentifier, $callIdItem, $itemVariable->getIdentifier());
        $variable = $this->buildItemVariable(
            $deliveryResultIdentifier,
            $test,
            $itemVariable,
            $item,
            $callIdItem
        );

        $saved = $this->persistVariable($keyStore, $variable);
        if ($saved){
            $this->storeReferenceOfKeysToResult($deliveryResultIdentifier, $keyStore);
        }

        return $saved;
    }

    /**
     * @param $deliveryResultIdentifier
     * @param $test
     * @param $item
     * @param array $itemVariables
     * @param $callIdItem
     * @return bool
     * @throws \Exception
     */
    public function storeItemVariables(
        $deliveryResultIdentifier,
        $test,
        $item,
        array $itemVariables,
        $callIdItem
    )
    {
        foreach ($itemVariables as $itemVariable) {
            $this->storeItemVariable(
                $deliveryResultIdentifier,
                $test,
                $item,
                $itemVariable,
                $callIdItem
            );
        }

        return true;
    }

    /**
     * @param string $deliveryResultIdentifier
     * @param string $test
     * @param taoResultServer_models_classes_Variable $testVariable
     * @param $callIdTest
     * @return bool
     * @throws \Exception
     */
    public function storeTestVariable(
        $deliveryResultIdentifier,
        $test,
        taoResultServer_models_classes_Variable $testVariable,
        $callIdTest
    )
    {
        $keyStore = $this->buildStoreKey($deliveryResultIdentifier, $callIdTest, $testVariable->getIdentifier());
        $variable = $this->buildTestVariable(
            $deliveryResultIdentifier,
            $test,
            $testVariable,
            $callIdTest
        );
        $saved = $this->persistVariable($keyStore, $variable);
        if ($saved){
            $this->storeReferenceOfKeysToResult($deliveryResultIdentifier, $keyStore);
        }
        return $saved;
    }

    /**
     * @param $deliveryResultIdentifier
     * @param $test
     * @param array $testVariables
     * @param $callIdTest
     * @return bool
     * @throws \Exception
     */
    public function storeTestVariables(
        $deliveryResultIdentifier,
        $test,
        array $testVariables,
        $callIdTest
    )
    {
        foreach ($testVariables as $testVariable) {
            $this->storeTestVariable(
                $deliveryResultIdentifier,
                $test,
                $testVariable,
                $callIdTest
            );
        }

        return true;
    }

    /**
     * @param array $callOptions
     */
    public function configure($callOptions = array())
    {
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
     * @param $keyStore
     * @param VariableStorable $variableStorable
     * @return bool
     * @throws \Exception
     */
    protected function persistVariable($keyStore, VariableStorable $variableStorable)
    {
        return $this->getPersistence()->set($keyStore, $this->encrypt(json_encode($variableStorable)));
    }

    /**
     * @param $deliveryResultIdentifier
     * @param $test
     * @param taoResultServer_models_classes_Variable $itemVariable
     * @param $item
     * @param $callIdItem
     * @return ItemVariableStorable
     */
    protected function buildItemVariable(
        $deliveryResultIdentifier,
        $test,
        $itemVariable,
        $item,
        $callIdItem
    )
    {
        if (!($itemVariable->isSetEpoch())) {
            $itemVariable->setEpoch(microtime());
        }

        return new ItemVariableStorable(
            $deliveryResultIdentifier, $test, $itemVariable, $item, $callIdItem
        );
    }

    /**
     * @param $deliveryResultIdentifier
     * @param $testIdentifier
     * @param taoResultServer_models_classes_Variable $testVariable
     * @param $callIdTest
     * @return TestVariableStorable
     */
    protected function buildTestVariable(
        $deliveryResultIdentifier,
        $testIdentifier,
        $testVariable,
        $callIdTest
    )
    {
        if (!($testVariable->isSetEpoch())) {
            $testVariable->setEpoch(microtime());
        }

        return new TestVariableStorable(
            $deliveryResultIdentifier,
            $testIdentifier,
            $testVariable,
            $callIdTest
        );
    }

    /**
     * @param $deliveryResultIdentifier
     * @param $id
     * @param $variable_id
     * @return string
     */
    protected function buildStoreKey($deliveryResultIdentifier, $id, $variable_id)
    {
        return md5($deliveryResultIdentifier . '_' . $id . '_'. $variable_id);
    }

    /**
     * @param $resultIdentifier
     * @param $keyStore
     * @throws \Exception
     */
    protected function storeReferenceOfKeysToResult($resultIdentifier, $keyStore)
    {
        $mapping = (string) $this->getPersistence()->get(self::PREFIX_DELIVERY_RESULTS_ITEMS . $resultIdentifier);
        $mapping = $mapping === '' ? [] : json_decode($mapping, true);

        if (!in_array($keyStore, $mapping)){
            $mapping[] = $keyStore;

            $this->getPersistence()->set(self::PREFIX_DELIVERY_RESULTS_ITEMS . $resultIdentifier, json_encode($mapping));
        }
    }
}