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
namespace oat\taoEncryption\ExtendedService;

use common_persistence_AdvKeyValuePersistence;
use common_persistence_Manager;
use oat\taoEncryption\Encryption\EncryptionServiceInterface;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use taoAltResultStorage_models_classes_KeyValueResultStorage;

class EncryptedKeyValueResultStorage
    extends taoAltResultStorage_models_classes_KeyValueResultStorage implements EncryptResult
{
    const OPTION_ENCRYPTION_SERVICE = 'asymmetricEncryptionService';

    /**
     * @var bool
     */
    private $skipEncrypting = false;

    /**
     * @return EncryptionServiceInterface
     */
    public function getEncryptionService()
    {
        /** @var EncryptionServiceInterface $service */
        $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_SERVICE)) ;

        return $service;
    }
    /**
     * @param $value
     * @return mixed
     */
    protected function unserializeVariableValue($value)
    {
        return parent::unserializeVariableValue($this->decryptVariable($value));
    }

    /**
     * @param $value
     * @return string
     */
    protected function serializeVariableValue($value)
    {
        if ($this->skipEncrypting) {
            return parent::serializeVariableValue($value);
        }

        return $this->encryptVariable(parent::serializeVariableValue($value));
    }

    /**
     * @param string $data
     * @return string
     */
    public function encryptVariable($data)
    {
        return $this->getEncryptionService()->encrypt($data);
    }

    /**
     * @inheritdoc
     */
    public function decryptVariable($data)
    {
        return $this->getEncryptionService()->decrypt($data);
    }

    /**
     * @param string $callId
     * @return bool|void
     */
    public function decryptAndSave()
    {
        $calls = $this->getAllCallIds();
        $this->skipEncrypting = true;

        if (!empty($calls)) {
            foreach ($calls as $callId) {
                $keyId = static::PREFIX_CALL_ID . $callId;
                try{
                    $variables = $this->getVariables($callId);
                }catch (DecryptionFailedException $exception){
                    //already decrypted skip.
                    continue;
                }

                $testVariables =[];
                $itemVariables =[];
                $deliveryResultIdentifier = null;
                $test = null;
                $callIdTest = null;
                $item = null;
                $callIdItem = null;

                foreach ($variables as $variable) {
                    $result = array_pop($variable);

                    $observed = $this->getPersistence()->hExists($keyId, $result->variable->getIdentifier());
                    if ($observed) {
                        if ($result->item === null){
                            $deliveryResultIdentifier = $result->deliveryResultIdentifier;
                            $test = $result->test;
                            $callIdTest = $result->callIdTest;

                            $testVariables[] = $result->variable;
                        } else {
                            $deliveryResultIdentifier = $result->deliveryResultIdentifier;
                            $test = $result->test;
                            $item = $result->item;
                            $callIdTest = $result->callIdTest;
                            $callIdItem = $result->callIdItem;

                            $itemVariables[] = $result->variable;
                        }
                    }
                }

                $deleted = $this->getPersistence()->del($keyId);

                if ($deleted) {
                    if (!empty($testVariables)) {
                        $this->storeTestVariables($deliveryResultIdentifier, $test, $testVariables, $callIdTest);
                    }

                    if (!empty($itemVariables)) {
                        $this->storeItemVariables($deliveryResultIdentifier, $test, $item, $itemVariables, $callIdItem);
                    }
                }
            }
        }
    }

    /**
     * Initialise the persistence and return it
     *
     * @return common_persistence_AdvKeyValuePersistence
     */
    private function getPersistence()
    {
        $perisistenceManager = $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);

        return $perisistenceManager->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }
}