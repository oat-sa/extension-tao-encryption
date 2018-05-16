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
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use common_report_Report as Report;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupUserApplicationKey'
 */
class SetupUserApplicationKey extends InstallAction
{

    /**
     * @param $params
     * @return Report
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function __invoke($params)
    {
        /** @var FileKeyProviderService $fileKeyProvider */
        $fileKeyProvider = $this->getServiceLocator()->get(FileKeyProviderService::SERVICE_ID);

        if ($fileKeyProvider->getKeyFromFileSystem() === ''){
            $fileKeyProvider->generateAndSaveKey();
            return Report::createSuccess('User Application key generated successfully.');
        }

        return Report::createInfo('User Application key already generated.');

    }
}