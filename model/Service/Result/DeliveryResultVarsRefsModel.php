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

class DeliveryResultVarsRefsModel
{
    const PREFIX_DELIVERY_RESULTS_ITEMS = 'encryptMappingResultsItems';

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
     * @param $resultIdentifier
     * @return array|mixed|string
     */
    public function getResultsVariablesRefs($resultIdentifier)
    {
        $mapping = (string) $this->persistence->get(self::PREFIX_DELIVERY_RESULTS_ITEMS . $resultIdentifier);
        $mapping = $mapping === '' ? [] : json_decode($mapping, true);

        return $mapping;
    }

    /**
     * @param $resultIdentifier
     * @param array $refs
     * @return bool
     * @throws \common_Exception
     */
    public function setResultsVariablesRefs($resultIdentifier, array $refs)
    {
        return $this->persistence->set(self::PREFIX_DELIVERY_RESULTS_ITEMS . $resultIdentifier, json_encode($refs));
    }

    /**
     * @param $reference
     * @return bool
     */
    public function deleteRef($reference)
    {
        return $this->persistence->del($reference);
    }

    /**
     * @param $resultId
     * @return bool
     */
    public function deleteRefs($resultId)
    {
        $refs = $this->getResultsVariablesRefs($resultId);
        foreach ($refs as $ref){
            $this->persistence->del($ref);
        }

        return $this->persistence->del(static::PREFIX_DELIVERY_RESULTS_ITEMS . $resultId);
    }

    /**
     * @param $resultId
     * @return bool
     */
    public function deleteResult($resultId)
    {
        return $this->persistence->del(static::PREFIX_DELIVERY_RESULTS_ITEMS . $resultId);
    }
}