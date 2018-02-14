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
namespace oat\taoEncryption\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\Service\EncryptionAsymmetricService;
use oat\taoEncryption\Service\Result\DecryptResultService;

/**
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\install\RegisterDecryptResultStorage'
 */
class RegisterDecryptResultStorage extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $report = Report::createSuccess();

        $persistenceId = 'kvMySql';

        try {
            \common_persistence_Manager::getPersistence($persistenceId);
        } catch (\common_Exception $e) {
            \common_persistence_Manager::addPersistence($persistenceId,  array(
                'driver' => 'SqlKvWrapper',
                'sqlPersistence' => 'default',
            ));
        }

        $decrypt = new DecryptResultService([
            DecryptResultService::OPTION_PERSISTENCE => $persistenceId,
            DecryptResultService::OPTION_ENCRYPTION_SERVICE => EncryptionAsymmetricService::SERVICE_ID,
        ]);

        $this->registerService(DecryptResultService::SERVICE_ID, $decrypt);

        return $report;
    }
}