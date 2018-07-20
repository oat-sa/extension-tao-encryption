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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoEncryption\scripts\tools;

use oat\oatbox\extension\InstallAction;
use common_report_Report as Report;
use oat\taoEncryption\Service\DeliveryLog\EncryptedDeliveryLogService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoSync\model\DeliveryLog\SyncDeliveryLogService;

/**
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedDeliveryLogService'
 */
class SetupEncryptedDeliveryLogService extends InstallAction
{
    /**
     * @param $params
     *
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $keyProvider = new SimpleKeyProviderService();
        $this->registerService(SimpleKeyProviderService::SERVICE_ID, $keyProvider);

        $stateStorage = $this->getServiceLocator()->get(DeliveryLog::SERVICE_ID);
        $options = $stateStorage->getOptions();

        $encryptedDeliveryLog = new EncryptedDeliveryLogService(array_merge($options, [
                EncryptedDeliveryLogService::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
                EncryptedDeliveryLogService::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => SimpleKeyProviderService::SERVICE_ID
            ])
        );

        $this->registerService(EncryptedDeliveryLogService::SERVICE_ID, $encryptedDeliveryLog);

        /** @var SyncDeliveryLogService $syncDeliveryLog */
        $syncDeliveryLog = $this->getServiceManager()->get(SyncDeliveryLogService::SERVICE_ID);
        $syncDeliveryLog->setOption(SyncDeliveryLogService::OPTION_SHOULD_DECODE_BEFORE_SYNC, false);
        $this->getServiceManager()->register(SyncDeliveryLogService::SERVICE_ID, $syncDeliveryLog);

        return Report::createSuccess('EncryptedDeliveryLogService configured and SyncDeliveryLogService should decode set to false');
    }
}