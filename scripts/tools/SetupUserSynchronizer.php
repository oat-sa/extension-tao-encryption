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
use oat\taoEncryption\Service\Sync\EncryptAdministratorSynchronizer;
use oat\taoEncryption\Service\Sync\EncryptProctorSynchronizer;
use oat\taoEncryption\Service\Sync\EncryptTestTakerSynchronizer;
use oat\taoSync\model\SyncService;
use common_report_Report as Report;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupUserSynchronizer'
 */
class SetupUserSynchronizer extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var SyncService $syncService */
        $syncService = $this->getServiceLocator()->get(SyncService::SERVICE_ID);
        $synchronizers = $syncService->getOption(SyncService::OPTION_SYNCHRONIZERS);

        $syncService->setOptions(array_merge(
           $syncService->getOptions(),
           [
               SyncService::OPTION_SYNCHRONIZERS => array_merge($synchronizers,[
                   'administrator' => EncryptAdministratorSynchronizer::SERVICE_ID,
                   'proctor' => EncryptProctorSynchronizer::SERVICE_ID,
                   'test-taker' => EncryptTestTakerSynchronizer::SERVICE_ID,
               ])
           ]
        ));

        $this->registerService(SyncService::SERVICE_ID, $syncService);

        return Report::createSuccess('Synchronizers: administrator, proctor, test-taker overwrite');

    }
}