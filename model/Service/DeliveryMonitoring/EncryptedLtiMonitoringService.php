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

namespace oat\taoEncryption\Service\DeliveryMonitoring;

use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Session\EncryptedLtiUser;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringService;
use oat\taoProctoring\model\monitorCache\implementation\MonitorCacheService;

class EncryptedLtiMonitoringService extends MonitorCacheService
{
    /**
     * @param array $criteria
     * @param array $options
     * @param bool $together
     * @return \oat\taoProctoring\model\monitorCache\implementation\DeliveryMonitoringData[]
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function find(array $criteria = [], array $options = [], $together = false)
    {
        $options['asArray'] = false;
        $result = parent::find($criteria, $options, $together);

        foreach ($result as $deliveryMonitoringData) {
            $row = $deliveryMonitoringData->get();
            if (isset($row[DeliveryMonitoringService::TEST_TAKER_FIRST_NAME])) {
                $decrypted = $this->decryptTestTakerInfo(
                    $row[DeliveryMonitoringService::TEST_TAKER_FIRST_NAME],
                    $this->getApplicationKey()
                );

                $deliveryMonitoringData->addValue(DeliveryMonitoringService::TEST_TAKER_FIRST_NAME, $decrypted, true);
            }
            if (isset($row[DeliveryMonitoringService::TEST_TAKER_LAST_NAME])) {
                $decrypted = $this->decryptTestTakerInfo(
                    $row[DeliveryMonitoringService::TEST_TAKER_LAST_NAME],
                    $this->getApplicationKey()
                );

                $deliveryMonitoringData->addValue(DeliveryMonitoringService::TEST_TAKER_LAST_NAME, $decrypted, true);

            }
        }

        return $result;
    }

    /**
     * @param $data
     * @param $appKey
     * @return string
     * @throws \Exception
     */
    protected function decryptTestTakerInfo($data, $appKey = null)
    {
        if (is_null($appKey)) {
            return $data;
        }

        /** @var EncryptionSymmetricService $encryptService */
        $encryptService = $this->getServiceLocator()->get(EncryptionSymmetricService::SERVICE_ID);

        /** @var SimpleKeyProviderService $simpleKeyProvider */
        $simpleKeyProvider = $this->getServiceLocator()->get(SimpleKeyProviderService::SERVICE_ID);
        $simpleKeyProvider->setKey($appKey);

        $encryptService->setKeyProvider($simpleKeyProvider);

        return $encryptService->decrypt(base64_decode($data));
    }

    /**
     * @return null|string
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    protected function getApplicationKey()
    {
        $user = \common_session_SessionManager::getSession()->getUser();

        if ($user instanceof EncryptedLtiUser) {
           return $user->getApplicationKey();
        }

        return null;
    }
}