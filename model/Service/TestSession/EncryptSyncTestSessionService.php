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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoEncryption\Service\TestSession;

use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoEncryption\Service\Mapper\TestSessionSyncMapper;
use oat\taoSync\model\TestSession\SyncTestSessionService;

class EncryptSyncTestSessionService extends SyncTestSessionService
{
    /**
     * @param DeliveryExecution $deliveryExecution
     * @return bool
     * @throws \Exception
     * @throws \common_Exception
     */
    protected function afterImportSession($deliveryExecution)
    {
       return $this->getTestSessionSyncMapper()->set($deliveryExecution->getIdentifier(), 1);
    }

    /**
     * @return array|TestSessionSyncMapper|object
     */
    private function getTestSessionSyncMapper()
    {
        return $this->getServiceLocator()->get(TestSessionSyncMapper::SERVICE_ID);
    }
}