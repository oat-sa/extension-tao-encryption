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

namespace oat\taoEncryption\Service\Lti;

use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchData;
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchDataStorage;
use oat\taoEncryption\Service\LtiConsumer\EncryptedLtiConsumer;
use oat\taoEncryption\Service\Session\EncryptedLtiUser;
use oat\taoLti\models\classes\LtiException;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\LtiUser;
use oat\taoLti\models\classes\user\LtiUserFactoryInterface;
use oat\taoLti\models\classes\user\LtiUserInterface;

class EncryptLtiUserFactoryService extends ConfigurableService implements LtiUserFactoryInterface
{
    const SERVICE_ID = 'taoEncryption/EncryptLtiUserFactory';

    const OPTION_LAUNCH_DATA_STORAGE = 'launchDataStorage';

    /** @var string */
    protected $applicationKey;

    /**
     * @param LtiLaunchData $ltiContext
     * @param string $userId
     * @return LtiUserInterface
     * @throws \Exception
     */
    public function create(LtiLaunchData $ltiContext, $userId)
    {
        $encryptedLtiContext = new EncryptedLtiLaunchData(
            $ltiContext,
            $this->getApplicationKey($ltiContext)
        );
        $this->propagate($encryptedLtiContext);

        $this->getLtiLaunchDataStorage()->save($encryptedLtiContext);
        return $this->propagate(new LtiUser($encryptedLtiContext, $userId));
    }

    /**
     * @param LtiLaunchData $launchData
     * @return string
     * @throws \common_Exception
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     * @throws \Exception
     */
    protected function getApplicationKey(LtiLaunchData $launchData)
    {
        if (is_null($this->applicationKey)) {
            $ltiConsumer = $launchData->getLtiConsumer();
            $value = $ltiConsumer->getUniquePropertyValue(
                new \core_kernel_classes_Property(EncryptedLtiConsumer::PROPERTY_ENCRYPTED_APPLICATION_KEY)
            );
            $appKey = $value->literal;
            if (!$launchData->hasVariable(EncryptedLtiUser::PARAM_CUSTOM_CUSTOMER_APP_KEY)) {
                throw new LtiException('Customer App Key needs to be set.');
            }

            $this->applicationKey = $this->decryptAppKey(
                $launchData->getVariable(EncryptedLtiUser::PARAM_CUSTOM_CUSTOMER_APP_KEY), $appKey
            );
        }

        return $this->applicationKey;
    }

    /**
     * @param $customerAppKey
     * @param $appKey
     * @return string
     * @throws \Exception
     */
    protected function decryptAppKey($customerAppKey, $appKey)
    {
        /** @var EncryptionSymmetricService $encryptService */
        $encryptService = $this->getServiceLocator()->get(EncryptionSymmetricService::SERVICE_ID);

        /** @var SimpleKeyProviderService $simpleKeyProvider */
        $simpleKeyProvider = $this->getServiceLocator()->get(SimpleKeyProviderService::SERVICE_ID);
        $simpleKeyProvider->setKey($customerAppKey);
        $encryptService->setKeyProvider($simpleKeyProvider);

        return $encryptService->decrypt(base64_decode($appKey));
    }

    /**
     * @return EncryptedLtiLaunchDataStorage
     */
    protected function getLtiLaunchDataStorage()
    {
        $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_LAUNCH_DATA_STORAGE));

        if (!$service instanceof EncryptedLtiLaunchDataStorage) {
            throw new \Exception('EncryptedLtiLaunchDataStorage not set');
        }

        return $service;
    }
}