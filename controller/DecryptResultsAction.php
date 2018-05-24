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

namespace oat\taoEncryption\controller;

use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoEncryption\Task\DecryptResultsTask;
use oat\taoTaskQueue\model\QueueDispatcher;
use tao_actions_CommonModule;

class DecryptResultsAction extends tao_actions_CommonModule
{
    /**
     * @return \oat\taoTaskQueue\model\Task\CallbackTask|\oat\taoTaskQueue\model\Task\CallbackTaskInterface
     */
    public function index()
    {
        $deliveriesIds = [];
        /** @var \core_kernel_classes_Resource $delivery */
        foreach ($this->getDeliveryAssemblyService()->getAllAssemblies() as $delivery) {
            $deliveriesIds[] =  $delivery->getUri();
        }

        $action = new DecryptResultsTask();
        $action->setServiceLocator($this->getServiceLocator());

        /** @var QueueDispatcher $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);
        $queueDispatcher->createTask($action, [
            DecryptResultsTask::OPTION_DELIVERY_IDS => $deliveriesIds
        ], 'Decrypting Results');

        $this->forwardUrl(_url('index', 'Results', 'taoOutcomeUi'));
    }

    /**
     * @return DeliveryAssemblyService
     */
    protected function getDeliveryAssemblyService()
    {
        return DeliveryAssemblyService::singleton();
    }
}