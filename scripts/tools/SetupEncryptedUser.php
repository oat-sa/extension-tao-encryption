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
use oat\generis\model\user\UserFactoryService;
use oat\oatbox\extension\InstallAction;
use common_report_Report as Report;
use oat\taoEncryption\Service\User\EncryptedUserFactoryService;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedUser'
 */
class SetupEncryptedUser extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var UserFactoryService $service */
        $factory = new EncryptedUserFactoryService([
            EncryptedUserFactoryService::OPTION_USER_CLASS_WRAPPED => \core_kernel_users_GenerisUser::class
        ]);

        $this->registerService(EncryptedUserFactoryService::SERVICE_ID, $factory);

        return Report::createSuccess('EncryptedUser setup.');

    }
}