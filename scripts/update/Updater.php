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
 *
 */
namespace oat\taoEncryption\scripts\update;

use common_ext_ExtensionUpdater;
use oat\taoEncryption\Service\KeyProvider\KeyProviderClient;

class Updater extends common_ext_ExtensionUpdater
{
    /**
     * @param $initialVersion
     * @return string|void
     * @throws \Exception
     */
    public function update($initialVersion)
    {
        $this->skip('0.1.0', '0.3.0');

        if ($this->isVersion('0.3.0')) {
            $this->getServiceManager()->register(KeyProviderClient::SERVICE_ID, new KeyProviderClient());
            $this->setVersion('0.4.0');
        }

    }
}