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

use oat\oatbox\service\ConfigurableService;
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoResultServer\models\Entity\TestVariableStorable;
use oat\taoResultServer\models\Entity\VariableStorable;
use oat\taoResultServer\models\Exceptions\DuplicateVariableException;
use taoResultServer_models_classes_WritableResultStorage;

class StoreVariableService extends ConfigurableService implements StoreVariableServiceInterface
{
    /**
     * @param $deliveryResultIdentifier
     * @param VariableStorable $resultRow
     * @param taoResultServer_models_classes_WritableResultStorage $resultStorage
     * @return bool
     * @throws DuplicateVariableException
     */
    public function save($deliveryResultIdentifier, VariableStorable $resultRow, taoResultServer_models_classes_WritableResultStorage $resultStorage)
    {

        if ($resultRow instanceof ItemVariableStorable) {
            $resultStorage->storeItemVariable(
                $deliveryResultIdentifier,
                $resultRow->getTestIdentifier(),
                $resultRow->getItemIdentifier(),
                $resultRow->getVariable(),
                $resultRow->getCallItemId() . '|' .$deliveryResultIdentifier
            );

        } else if ($resultRow instanceof TestVariableStorable) {
            $resultStorage->storeTestVariable(
                $deliveryResultIdentifier,
                $resultRow->getTestIdentifier(),
                $resultRow->getVariable(),
                $deliveryResultIdentifier
            );
        }

        return true;
    }
}