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
namespace oat\taoEncryption\Service\Result;

use common_persistence_KeyValuePersistence;

class DeliveryResultsModel
{
    const PREFIX_DELIVERY_RESULTS = 'encryptDeliveryResults';

    /** @var common_persistence_KeyValuePersistence */
    private $persistence;

    /**
     * @param $persistence
     */
    public function __construct(common_persistence_KeyValuePersistence $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param $deliveryIdentifier
     * @return mixed
     * @throws \Exception
     */
    public function getResultsReferences($deliveryIdentifier)
    {
        $results = (string)$this->persistence->get(static::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier);
        $results = $results === '' ? [] : json_decode($results, true);

        return $results;
    }

    /**
     * @param $deliveryIdentifier
     * @param array $results
     * @return bool
     * @throws \common_Exception
     * @throws \Exception
     */
    public function setResultsReferences($deliveryIdentifier, array $results)
    {
        return $this->persistence->set(
            static::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier,
            json_encode($results)
        );
    }

    /**
     * @param string $deliveryIdentifier
     * @return bool
     */
    public function deleteResultsReference($deliveryIdentifier)
    {
        return $this->persistence->del(static::PREFIX_DELIVERY_RESULTS . $deliveryIdentifier);
    }
}