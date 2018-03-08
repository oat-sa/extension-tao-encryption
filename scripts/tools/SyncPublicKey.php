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
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use oat\taoSync\model\event\SynchronisationStart;
use common_report_Report as Report;

class SyncPublicKey extends InstallAction
{
    public function __invoke($params)
    {
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        if ($extensionManager->isInstalled('taoSync')) {
            $this->registerEvent(SynchronisationStart::class, [AsymmetricKeyPairProviderService::class, 'onSynchronisationStarted']);
            return Report::createSuccess('Synchronisation event successfully configured to synchronize encryption key.');
        }
        return Report::createSuccess('taoSync extension is not installed. No need to configure event to synchronize encryption key.');

    }

}