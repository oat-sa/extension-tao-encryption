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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoEncryption\Service\Result;

use common_persistence_KeyValuePersistence;
use common_persistence_KvDriver;
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoResultServer\models\Entity\TestVariableStorable;
use oat\taoSync\model\Result\SyncResultDataFormatter;

class SyncEncryptedResultDataFormatter extends SyncResultDataFormatter
{
    const OPTION_PERSISTENCE = 'persistence';

    /** @var  common_persistence_KvDriver*/
    private $persistence;

    /** @var DeliveryResultVarsRefsModel */
    private $deliveryResultVarsRefs;

    /**
     * Get variables of a delivery execution
     *
     * @param $deliveryId
     * @param $deliveryExecutionId
     * @return array
     * @throws \Exception
     */
    protected function getDeliveryExecutionVariables($deliveryId, $deliveryExecutionId)
    {
        $refs = $this->getDeliveryResultVarsRefsModel()->getResultsVariablesRefs($deliveryExecutionId);

        $resultRows = [];
        foreach ($refs as $ref) {
            $resultRows[$ref] = $this->getResultRow($ref);
        }

        return $resultRows;
    }

    /**
     * @return DeliveryResultVarsRefsModel
     * @throws \Exception
     */
    protected function getDeliveryResultVarsRefsModel()
    {
        if (is_null($this->deliveryResultVarsRefs)){
            $this->deliveryResultVarsRefs = new DeliveryResultVarsRefsModel($this->getPersistence());
        }

        return $this->deliveryResultVarsRefs;
    }

    /**
     * @throws \Exception
     * @return common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)){
            $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
            $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);

            if (!$persistence instanceof common_persistence_KeyValuePersistence) {
                throw new \Exception('Only common_persistence_KeyValuePersistence supported');
            }

            $this->persistence = $persistence;
        }

        return $this->persistence;
    }

    /**
     * @param $ref
     * @return bool|DecryptResultService|ItemVariableStorable|TestVariableStorable
     * @throws \Exception
     */
    protected function getResultRow($ref)
    {
        return $this->getPersistence()->get($ref);
    }
}