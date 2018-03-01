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

use core_kernel_classes_Literal;
use oat\oatbox\log\LoggerAwareTrait;
use oat\taoEncryption\Rdf\EncryptedUserRdf;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoSync\model\synchronizer\user\UserSynchronizer;

abstract class EncryptUserSynchronizer extends UserSynchronizer
{
    use LoggerAwareTrait;

    const OPTION_ENCRYPTION_SERVICE = 'symmetricEncryptionService';

    const OPTION_ENCRYPTED_PROPERTIES = 'encryptedProperties';

    const OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE = 'keyProviderService';

    /** @var EncryptionSymmetricService */
    private $encryptionService;

    /**
     * @param string $key
     * @return EncryptionSymmetricService
     * @throws \Exception
     */
    public function getEncryptionService($key)
    {
        if (is_null($this->encryptionService) ) {
            /** @var EncryptionSymmetricService $service */
            $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_SERVICE));
            if (!$service instanceof EncryptionSymmetricService) {
                throw new  \Exception('Incorrect algorithm service provided');
            }

            $this->encryptionService = $service;
        }

        /** @var SimpleKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE));
        $keyProvider->setKey($key);
        $this->encryptionService->setKeyProvider($keyProvider);

        return $this->encryptionService;
    }

    /**
     * @param $properties
     * @return array
     * @throws \Exception
     */
    public function decryptProperties($properties)
    {
        $propertiesToEncrypt = $this->getEncryptedProperties();
        $encryptedProperties = [];
        $keyEncryption = $properties[EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY];

        foreach ($properties as $key => $value){
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
                $encryptedProperties[$key] = $valuesDecrypted;
            } else {
                $encryptedProperties[$key] = $value;
            }
        }

        return $encryptedProperties;
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
        $encryptedProperties = [];
        $propertiesToEncrypt = $this->getEncryptedProperties();
        $keyEncryption = $properties[EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY];

        foreach ($properties as $key => $value){
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
     * Format a resource to an array
     *
     * Add a checksum to identify the resource content
     * Add resource triples as properties if $withProperties param is true
     *
     * @param \core_kernel_classes_Resource $resource
     * @param $withProperty
     * @return array
     * @throws \Exception
     */
    public function format(\core_kernel_classes_Resource $resource, $withProperty = false)
    {
        $properties = $this->filterProperties($resource->getRdfTriples()->toArray());
        $properties = $this->encryptProperties($properties);

        return [
            'id' => $resource->getUri(),
            'checksum' => md5(serialize($properties)),
            'properties' => ($withProperty === true) ? $properties : [],
        ];
    }
}