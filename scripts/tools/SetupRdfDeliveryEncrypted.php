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

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\Service\Sync\EncryptDeliverySynchronizerService;
use oat\taoEncryption\Service\Sync\EncryptRdfDeliverySyncFormatter;
use oat\taoSync\model\synchronizer\delivery\RdfDeliverySynchronizer;
use oat\taoSync\model\SyncService;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupRdfDeliveryEncrypted'
 */
class SetupRdfDeliveryEncrypted extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);

        if (!$extensionManager->isInstalled('taoSync')) {
            return Report::createSuccess('Cannot setup sync, to taoSync extension installed');
        }

        $rdfDeliverySync = new EncryptRdfDeliverySyncFormatter([]);
        $this->registerService(EncryptRdfDeliverySyncFormatter::SERVICE_ID, $rdfDeliverySync);

        /** @var SyncService $syncService */
        $syncService = $this->getServiceLocator()->get(SyncService::SERVICE_ID);
        $synchronizers = $syncService->getOption(SyncService::OPTION_SYNCHRONIZERS);
        $syncOptions = $syncService->getOptions();

        $syncService->setOptions(array_merge(
            $syncOptions,
            [
                SyncService::OPTION_SYNCHRONIZERS => array_merge($synchronizers,[
                    'delivery' => new RdfDeliverySynchronizer(array_merge(
                        $syncOptions[SyncService::OPTION_SYNCHRONIZERS]['delivery']->getOptions(),
                        [RdfDeliverySynchronizer::OPTIONS_FORMATTER_CLASS => EncryptRdfDeliverySyncFormatter::SERVICE_ID]
                    )),
                ])
            ]
        ));

        $this->registerService(SyncService::SERVICE_ID, $syncService);

        $encryptDeliverySinchronize = new EncryptDeliverySynchronizerService();

        $this->registerService(EncryptDeliverySynchronizerService::SERVICE_ID, $encryptDeliverySinchronize);

        return Report::createSuccess('EncryptRdfDeliverySynchronizer and EncryptDeliverySynchronizerService setup');
    }

}