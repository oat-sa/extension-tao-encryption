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

namespace oat\taoEncryption\Service\DeliveryLog;

use common_session_SessionManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\user\AnonymousUser;
use oat\taoEncryption\Service\EncryptionSymmetricServiceHelper;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Session\EncryptedUser;
use oat\taoProctoring\model\deliveryLog\implementation\RdsDeliveryLogService;

class EncryptedDeliveryLogService extends RdsDeliveryLogService
{
    use LoggerAwareTrait;
    use EncryptionSymmetricServiceHelper;

    const OPTION_ENCRYPTION_SERVICE = 'symmetricEncryptionService';

    const OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE = 'keyProviderService';

    /**
     * @param $data
     * @return array
     * @throws \Exception
     */
    protected function decodeData($data)
    {
        $data = $this->getEncryptionService($this->getApplicationKey())->decrypt($data);

        return parent::decodeData(base64_decode($data));
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     * @throws \common_exception_Error
     */
    protected function encodeData($data)
    {
        $data = parent::encodeData($data);

        $data = $this->getEncryptionService($this->getApplicationKey())->encrypt($data);

        return base64_encode($data);
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \common_exception_Error
     */
    protected function getApplicationKey()
    {
        $user = $this->getUser();

        if ($user instanceof AnonymousUser){
            /** @var SimpleKeyProviderService $keyProvider */
            $keyProvider = $this->getServiceLocator()->get($this->getOptionEncryptionKeyProvider());

            return base64_decode($keyProvider->getKey()->getKey());
        }

        if (!$user instanceof EncryptedUser){
            throw new \Exception('EncryptedStateStorage should work only with EncryptedUser');
        }

        return $user->getApplicationKey();
    }

    /**
     * @return \oat\oatbox\user\User
     * @throws \common_exception_Error
     */
    protected function getUser()
    {
        return common_session_SessionManager::getSession()->getUser();
    }

    /**
     * @return string
     */
    protected function getOptionEncryptionService()
    {
        return $this->getOption(static::OPTION_ENCRYPTION_SERVICE);
    }

    /**
     * @return string
     */
    protected function getOptionEncryptionKeyProvider()
    {
        return $this->getOption(static::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE);
    }
}