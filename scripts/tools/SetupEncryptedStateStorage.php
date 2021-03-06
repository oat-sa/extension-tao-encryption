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
use common_report_Report as Report;
use oat\tao\model\state\StateStorage;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\SessionState\EncryptedStateStorage;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedStateStorage'
 */
class SetupEncryptedStateStorage extends InstallAction
{
    /**
     * @param $params
     *
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $keyProvider = new SimpleKeyProviderService();
        $this->registerService(SimpleKeyProviderService::SERVICE_ID, $keyProvider);

        /** @var StateStorage $stateStorage */
        $stateStorage = $this->getServiceLocator()->get(StateStorage::SERVICE_ID);
        $options = $stateStorage->getOptions();

        $encryptedStateStorage = new EncryptedStateStorage(array_merge($options, [
                EncryptedStateStorage::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
                EncryptedStateStorage::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => SimpleKeyProviderService::SERVICE_ID
            ])
        );

        $this->registerService(EncryptedStateStorage::SERVICE_ID, $encryptedStateStorage);

        return Report::createSuccess('EncryptedStateStorage configured');
    }
}