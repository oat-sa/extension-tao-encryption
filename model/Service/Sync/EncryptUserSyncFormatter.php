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

namespace oat\taoEncryption\Service\Sync;

use oat\oatbox\log\LoggerAwareTrait;
use oat\taoEncryption\Service\EncryptionSymmetricServiceHelper;
use core_kernel_classes_Literal;
use oat\taoEncryption\Rdf\EncryptedUserRdf;
use oat\taoSync\model\formatter\FormatterService;

class EncryptUserSyncFormatter extends FormatterService
{
    use LoggerAwareTrait;
    use EncryptionSymmetricServiceHelper;

    const SERVICE_ID = 'taoEncryption/encryptUserSyncFormatter';

    const OPTION_ENCRYPTION_SERVICE = 'symmetricEncryptionService';

    const OPTION_ENCRYPTED_PROPERTIES = 'encryptedProperties';

    const OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE = 'keyProviderService';

    /**
     * @param $properties
     * @return array
     * @throws \Exception
     */
    public function decryptProperties($properties)
    {
        $propertiesToEncrypt = $this->getEncryptedProperties();
        $decryptedProperties = [];
        if (!isset($properties[EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY])){
            return $properties;
        }

        $keyEncryption = $properties[EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY];

        foreach ($properties as $key => $value){
            if ($key === EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY){
                continue;
            }
            if (in_array($key, $propertiesToEncrypt)){
                $valuesDecrypted = [];
                if (is_array($value)){
                    foreach ($value as $encryptVal){
                        $valuesDecrypted[] = new core_kernel_classes_Literal(
                            $this->getEncryptionService($keyEncryption)->decrypt(base64_decode($encryptVal))
                        );
                    }

                } else {
                    $valuesDecrypted = new core_kernel_classes_Literal(
                        $this->getEncryptionService($keyEncryption)->decrypt(base64_decode($value))
                    );
                }
                $decryptedProperties[$key] = $valuesDecrypted;
            } else {
                $decryptedProperties[$key] = $value;
            }
        }

        return $decryptedProperties;
    }


    /**
     * @return array
     */
    protected function getEncryptedProperties()
    {
        return $this->getOption(static::OPTION_ENCRYPTED_PROPERTIES);
    }

    /**
     * @param array $properties
     * @return array
     * @throws \Exception
     */
    public function encryptProperties(array $properties)
    {
        if (!isset($properties[EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY])){
            return $properties;
        }

        $encryptedProperties = [];
        $propertiesToEncrypt = $this->getEncryptedProperties();
        $keyEncryption = $properties[EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY];

        foreach ($properties as $key => $value){
            if ($key === EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY){
                continue;
            }
            if (in_array($key, $propertiesToEncrypt)){
                $valuesEncrypted = [];
                if (is_array($value)){
                    foreach ($value as $encryptVal){
                        $valuesEncrypted[] = base64_encode($this->getEncryptionService($keyEncryption)->encrypt($encryptVal));
                    }
                } else {
                    $valuesEncrypted = base64_encode($this->getEncryptionService($keyEncryption)->encrypt($value));
                }
                $encryptedProperties[$key] = $valuesEncrypted;
            } else {
                $encryptedProperties[$key] = $value;
            }
        }

        return $encryptedProperties;
    }

    /**
     * @param array $triples
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function filterProperties(array $triples, array $options = [])
    {
        $properties = $this->callParentFilterProperties($triples, $options);
        $properties = $this->encryptProperties($properties);

        return $properties;
    }

    /**
     * @param array $triples
     * @param array $options
     * @return array
     */
    protected function callParentFilterProperties(array $triples, array $options = [])
    {
        return parent::filterProperties($triples, $options);
    }

    /**
     * @inheritdoc
     */
    protected function getOptionEncryptionService()
    {
        return $this->getOption(static::OPTION_ENCRYPTION_SERVICE);
    }

    /**
     * @inheritdoc
     */
    protected function getOptionEncryptionKeyProvider()
    {
        return $this->getOption(static::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE);
    }
}