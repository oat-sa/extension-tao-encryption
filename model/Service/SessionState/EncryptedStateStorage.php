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
namespace oat\taoEncryption\Service\SessionState;

use core_kernel_classes_Property;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\log\LoggerAwareTrait;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use tao_models_classes_service_StateStorage;

class EncryptedStateStorage extends tao_models_classes_service_StateStorage
{
    use OntologyAwareTrait;
    use LoggerAwareTrait;

    const OPTION_ENCRYPTION_SERVICE = 'symmetricEncryptionService';

    const OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE = 'keyProviderService';

    /** @var SimpleKeyProviderService */
    private $encryptionService;

    /**
     * @param string $key
     * @return EncryptionSymmetricService
     * @throws \Exception
     */
    public function getEncryptionService($key)
    {
        if (is_null($this->encryptionService)) {
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
     * @param string $userId
     * @param string $callId
     * @param string $data
     * @return bool
     * @throws \Exception
     */
    public function set($userId, $callId, $data)
    {
        return parent::set($userId, $callId, base64_encode($this->getEncryptionService($this->getUserPassword($userId))->encrypt($data)));
    }

    /**
     * @param string $userId
     * @param string $callId
     * @return string
     * @throws \Exception
     */
    public function get($userId, $callId)
    {
        $value = parent::get($userId, $callId);
        if (is_null($value)) {
            return null;
        }

        return $this->getEncryptionService($this->getUserPassword($userId))->decrypt(base64_decode($value));
    }

    /**
     * @param string $userId
     * @return string
     */
    private function getUserPassword($userId)
    {
        try {
            $userResource = $this->getResource($userId);
            $password = $userResource->getUniquePropertyValue(
                new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_PASSWORD)
            );

        }catch (\common_Exception $exception){
            $this->logAlert('User has no password to encrypt');

            return '';
        }

        return $password->literal;
    }
}