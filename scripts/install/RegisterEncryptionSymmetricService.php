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
use oat\taoEncryption\Encryption\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Encryption\EncryptionSymmetricService;
use oat\taoEncryption\Encryption\KeyProvider\PasswordKeyProviderService;

class RegisterEncryptionSymmetricService extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $algo = new AlgorithmSymmetricService([
            AlgorithmSymmetricService::OPTION_ALGORITHM => 'RC4'
        ]);
        $this->registerService(AlgorithmSymmetricService::SERVICE_ID, $algo);

        $keyProvider = new PasswordKeyProviderService();
        $this->registerService(PasswordKeyProviderService::SERVICE_ID, $keyProvider);

        $encryption = new EncryptionSymmetricService([
            EncryptionSymmetricService::OPTION_ENCRYPTION_ALGORITHM => 'taoEncryption/symmetricAlgorithm',
            EncryptionSymmetricService::OPTION_KEY_PROVIDER => 'taoEncryption/symmetricPasswordProvider',
        ]);

        $this->registerService(EncryptionSymmetricService::SERVICE_ID, $encryption);

        return \common_report_Report::createSuccess('EncryptionSymmetricService successfully registered.');

    }
}