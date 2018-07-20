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
use oat\taoEncryption\Service\DeliveryLog\DecryptDeliveryLogFormatterService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoSync\model\DeliveryLog\DeliveryLogFormatterService;

/**
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupDecryptDeliveryLogFormatterService'
 */
class SetupDecryptDeliveryLogFormatterService extends InstallAction
{
    /**
     * @param $params
     *
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var DeliveryLogFormatterService $deliveryLogFormatter */
        $deliveryLogFormatter = $this->getServiceLocator()->get(DeliveryLogFormatterService::SERVICE_ID);
        $options = $deliveryLogFormatter->getOptions();

        $encryptedDeliveryLog = new DecryptDeliveryLogFormatterService(array_merge($options, [
                DecryptDeliveryLogFormatterService::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
                DecryptDeliveryLogFormatterService::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => FileKeyProviderService::SERVICE_ID
            ])
        );

        $this->registerService(DecryptDeliveryLogFormatterService::SERVICE_ID, $encryptedDeliveryLog);

        return Report::createSuccess('DecryptDeliveryLogFormatterService configured');
    }
}