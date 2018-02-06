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

use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoEncryption\Encryption\KeyProvider\AsymmetricKeyPairProviderService;

class RegisterKeyPairProviderService extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var FileSystemService $fileSystem */
        $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        if (! $fileSystem->hasDirectory('keysEncryption')) {
            $fileSystem->createFileSystem('keysEncryption');
            $this->registerService(FileSystemService::SERVICE_ID, $fileSystem);
        }

        $keyPairProvider = new AsymmetricKeyPairProviderService([
            AsymmetricKeyPairProviderService::OPTION_FILE_SYSTEM_ID => 'keysEncryption'
        ]);

        $this->registerService(AsymmetricKeyPairProviderService::SERVICE_ID, $keyPairProvider);

        return \common_report_Report::createSuccess('AsymmetricKeyPairProviderService successfully registered.');
    }
}