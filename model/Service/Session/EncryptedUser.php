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

use core_kernel_classes_Literal;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_users_GenerisUser;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdf;
use oat\oatbox\service\ServiceManager;
use oat\taoEncryption\Rdf\EncryptedUserRdf;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Sync\EncryptTestTakerSynchronizer;

class EncryptedUser extends core_kernel_users_GenerisUser
{
    /** @var string */
    private $key;

    /** @var string */
    protected $applicationKey;

    /**
     * EncryptedUser constructor.
     * @param core_kernel_classes_Resource $user
     * @param null $passwordPlain
     * @throws \Exception
     */
    public function __construct(core_kernel_classes_Resource $user, $passwordPlain = null)
    {
        parent::__construct($user);

        $password = $this->getPropertyValues(GenerisRdf::PROPERTY_USER_PASSWORD);
        $salt     = $password[0];
        $this->key = GenerateKey::generate($passwordPlain, $salt);

        /** @var EncryptionSymmetricService $encryptService */
        $encryptService = ServiceManager::getServiceManager()->get(EncryptionSymmetricService::SERVICE_ID);
        /** @var SimpleKeyProviderService $simpleKeyProvider */
        $simpleKeyProvider = ServiceManager::getServiceManager()->get(SimpleKeyProviderService::SERVICE_ID);
        $simpleKeyProvider->setKey($passwordPlain);
        $encryptService->setKeyProvider($simpleKeyProvider);

        $appKey = $this->getPropertyValues(EncryptedUserRdf::PROPERTY_ENCRYPTION_PUBLIC_KEY);
        $appKey = $appKey[0];

        $this->applicationKey = $encryptService->decrypt(base64_decode($appKey));
    }

    /**
     * @param $property
     * @return array
     * @throws \common_Exception
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \core_kernel_persistence_Exception
     * @throws \Exception
     */
    protected function getUncached($property)
    {
        switch ($property) {
            case GenerisRdf::PROPERTY_USER_DEFLG:
            case GenerisRdf::PROPERTY_USER_UILG:
                $resource = $this->getUserResource()->getOnePropertyValue(new core_kernel_classes_Property($property));
                if (!is_null($resource)) {
                    if ($resource instanceof core_kernel_classes_Resource) {
                        $val = $resource->getUniquePropertyValue(new core_kernel_classes_Property(OntologyRdf::RDF_VALUE));
                        $decryptVal = $this->getDecryptionService()->decryptProperties([
                            EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => $this->key,
                            $property => $val->literal
                        ]);
                        return array(
                            new core_kernel_classes_Literal($decryptVal[$property])
                        );
                    } else {
                        return array(DEFAULT_LANG);
                    }
                } else {
                    return array(DEFAULT_LANG);
                }
                break;
            default:
                $val = $this->getUserResource()->getPropertyValues(new core_kernel_classes_Property($property));
                $decryptVal = $this->getDecryptionService()->decryptProperties([
                    EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => $this->key,
                    $property => $val
                ]);
                return $decryptVal[$property];
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __sleep()
    {
        $this->cache = $this->getDecryptionService()->encryptProperties(array_merge(
            $this->cache, [EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => $this->key]
        ));

        return array(
            'key',
            'applicationKey',
            'userResource',
            'cache',
            'cachedProperties'
        );
    }

    /**
     * @throws \Exception
     */
    public function __wakeup()
    {
        $this->cache = $this->getDecryptionService()->decryptProperties(array_merge(
            $this->cache, [EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => $this->key]
        ));
    }

    /**
     * @param string $key
     * @return \oat\oatbox\service\ConfigurableService|EncryptTestTakerSynchronizer
     * @throws \Exception
     */
    public function getDecryptionService()
    {
        return ServiceManager::getServiceManager()->get(EncryptTestTakerSynchronizer::SERVICE_ID);
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