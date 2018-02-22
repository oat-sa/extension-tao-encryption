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
use oat\taoEncryption\Service\Algorithm\AlgorithmAsymmetricRSAService;
use oat\taoEncryption\Service\EncryptionAsymmetricService;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;

class RegisterEncryptionAsymmetricService extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $algorithm = new AlgorithmAsymmetricRSAService([
            AlgorithmAsymmetricRSAService::OPTION_ALGORITHM => 'AES'
        ]);
        $this->registerService(AlgorithmAsymmetricRSAService::SERVICE_ID, $algorithm);

        $encryption = new EncryptionAsymmetricService([
            EncryptionAsymmetricService::OPTION_ENCRYPTION_ALGORITHM => AlgorithmAsymmetricRSAService::SERVICE_ID,
            EncryptionAsymmetricService::OPTION_KEY_PAIR_PROVIDER => AsymmetricKeyPairProviderService::SERVICE_ID
        ]);

        $this->registerService(EncryptionAsymmetricService::SERVICE_ID, $encryption);

        return \common_report_Report::createSuccess('EncryptionAsymmetricService successfully registered.');
    }
}