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

namespace oat\taoEncryption\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\LtiConsumer\EncryptLtiConsumerFormatterService;

class RegisterLtiConsumerFormatterService extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $formatterService = new EncryptLtiConsumerFormatterService([
            EncryptLtiConsumerFormatterService::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
            EncryptLtiConsumerFormatterService::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => FileKeyProviderService::SERVICE_ID,
        ]);

        $this->getServiceManager()->register(EncryptLtiConsumerFormatterService::SERVICE_ID, $formatterService);

        return \common_report_Report::createSuccess('EncryptLtiConsumerFormatterService successfully registered.');
    }
}
