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

namespace oat\taoEncryption\Service\Session;

use common_user_User;
use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ServiceManager;
use oat\taoEncryption\Rdf\EncryptedUserRdf;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Sync\EncryptUserSyncFormatter;

class EncryptedUser extends common_user_User
{
    /** @var string */
    private $key;

    /** @var string */
    protected $applicationKey;

    /** @var \common_user_User */
    protected $realUser;

    /**
     * EncryptedUser constructor.
     * @param common_user_User $user
     * @param null $hashForEncryption
     * @throws \Exception
     */
    public function __construct(common_user_User $user, $hashForEncryption = null)
    {
        $this->realUser = $user;

        $password = $this->realUser->getPropertyValues(GenerisRdf::PROPERTY_USER_PASSWORD);
        if (isset($password[0])){
            $salt     = $password[0];
            $this->key = GenerateKey::generate($hashForEncryption, $salt);
        }

        /** @var EncryptionSymmetricService $encryptService */
        $encryptService = ServiceManager::getServiceManager()->get(EncryptionSymmetricService::SERVICE_ID);
        /** @var SimpleKeyProviderService $simpleKeyProvider */
        $simpleKeyProvider = ServiceManager::getServiceManager()->get(SimpleKeyProviderService::SERVICE_ID);
        $simpleKeyProvider->setKey($hashForEncryption);
        $encryptService->setKeyProvider($simpleKeyProvider);

        $appKey = $this->getPropertyValues(EncryptedUserRdf::PROPERTY_ENCRYPTION_PUBLIC_KEY);
        if (isset($appKey[0])){
            $appKey = $appKey[0];

            $this->applicationKey = $encryptService->decrypt(base64_decode($appKey));
        }
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->realUser->getIdentifier();
    }

    /**
     * @param $property
     * @return array
     * @throws \Exception
     */
    public function getPropertyValues($property)
    {
        $values = $this->realUser->getPropertyValues($property);
        $decryptVal = $this->getDecryptionService()->decryptProperties([
            EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => $this->key,
            $property => $values
        ]);

        return $decryptVal[$property];
    }

    public function refresh()
    {
        $this->realUser->refresh();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __sleep()
    {
        return array(
            'key',
            'applicationKey',
            'realUser',
        );
    }

    /**
     * @param string $key
     * @return \oat\oatbox\service\ConfigurableService|EncryptUserSyncFormatter
     * @throws \Exception
     */
    public function getDecryptionService()
    {
        return ServiceManager::getServiceManager()->get(EncryptUserSyncFormatter::SERVICE_ID);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getApplicationKey()
    {
        return $this->applicationKey;
    }
}