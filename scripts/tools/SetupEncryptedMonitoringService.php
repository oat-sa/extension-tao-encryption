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

use oat\generis\model\GenerisRdf;
use oat\oatbox\extension\InstallAction;
use common_report_Report as Report;
use oat\taoEncryption\Service\DeliveryMonitoring\EncryptedMonitoringService;
use oat\taoProctoring\model\monitorCache\implementation\MonitorCacheService;

/**
 * Class SetupEncryptedMonitoringService
 * @package oat\taoEncryption\scripts\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedMonitoringService'
 */
class SetupEncryptedMonitoringService extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);

        if (!$extensionManager->isInstalled('taoProctoring')) {
            return Report::createSuccess('Cannot setup because taoProctoring not installed.');
        }

        $proctoringExtension = $extensionManager->getExtensionById('taoProctoring');
        $proctoringExtension->setConfig('monitoringUserExtraFields', [
            EncryptedMonitoringService::TEST_TAKER_LOGIN => GenerisRdf::PROPERTY_USER_LOGIN
        ]);

        /** @var MonitorCacheService $monitoringService */
        $monitoringService = $this->getServiceLocator()->get(MonitorCacheService::SERVICE_ID);
        $options = $monitoringService->getOptions();

        $encryptMonitoringService = new EncryptedMonitoringService($options);

        $this->registerService(EncryptedMonitoringService::SERVICE_ID, $encryptMonitoringService);

       return Report::createSuccess('EncryptedMonitoringService setup with success');
    }
}