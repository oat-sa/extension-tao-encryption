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
use oat\taoSync\model\synchronizer\delivery\RdfDeliverySynchronizer;

class EncryptRdfDeliverySynchronizer extends RdfDeliverySynchronizer
{
    const SERVICE_ID = 'taoEncryption/encryptRdfDeliverySynchronizer';

    /** @var array */
    private $properties;

    /**
     * @param array $triples
     * @return array
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function filterProperties(array $triples)
    {
        $properties = parent::filterProperties($triples);
        $this->properties = $properties;

        /** @var FileKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get(FileKeyProviderService::SERVICE_ID);
        $properties[EncryptedDeliveryRdf::PROPERTY_APPLICATION_KEY] = $keyProvider->getKeyFromFileSystem();

        return $properties;
    }

    /**
     * @param array $properties
     * @return string
     */
    protected function serializeProperties($properties)
    {
        if (is_null($this->properties)){
            return parent::serializeProperties($properties);
        }

        return parent::serializeProperties($this->properties);
    }
}