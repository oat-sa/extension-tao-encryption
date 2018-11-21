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
use oat\taoEncryption\Service\Lti\EncryptLtiUserFactoryService;
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchDataStorage;
use common_report_Report as Report;
use oat\taoLti\models\classes\user\LtiUserService;

/**
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptLtiUserFactoryService'
 */
class SetupEncryptLtiUserFactoryService extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $service = new EncryptLtiUserFactoryService([
            EncryptLtiUserFactoryService::OPTION_LAUNCH_DATA_STORAGE => EncryptedLtiLaunchDataStorage::SERVICE_ID
        ]);

        $this->getServiceManager()->register(EncryptLtiUserFactoryService::SERVICE_ID, $service);

        /** @var LtiUserService $ltiUserService */
        $ltiUserService = $this->getServiceManager()->get(LtiUserService::SERVICE_ID);
        $ltiUserService->setOption(LtiUserService::OPTION_FACTORY_LTI_USER, EncryptLtiUserFactoryService::SERVICE_ID);
        $this->getServiceManager()->register(LtiUserService::SERVICE_ID, $ltiUserService);

        return Report::createSuccess('SetupEncryptLtiUserFactoryService setup.');
    }
}