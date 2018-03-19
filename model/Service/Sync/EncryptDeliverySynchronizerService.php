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

namespace oat\taoEncryption\Service\Sync;

use oat\taoEncryption\Rdf\EncryptedDeliveryRdf;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoSync\model\synchronizer\delivery\DeliverySynchronizerService;

class EncryptDeliverySynchronizerService extends DeliverySynchronizerService
{
    /**
     * @param \core_kernel_classes_Resource $delivery
     * @return \core_kernel_classes_Resource
     * @throws \common_Exception
     * @throws \core_kernel_persistence_Exception
     */
    protected function importRemoteDeliveryTest(\core_kernel_classes_Resource $delivery)
    {
        $property = $this->getProperty(EncryptedDeliveryRdf::PROPERTY_APPLICATION_KEY);
        $key = $delivery->getOnePropertyValue($property);
        $delivery->removePropertyValue($property, $key);

        /** @var FileKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get(FileKeyProviderService::SERVICE_ID);
        $keyProvider->setKey($key);

        return parent::importRemoteDeliveryTest($delivery);
    }
}