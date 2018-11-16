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
namespace oat\taoEncryption\Test\Service\DeliveryMonitoring;

use core_kernel_classes_Resource;
use oat\oatbox\user\User;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoEncryption\Service\DeliveryMonitoring\EncryptedMonitoringService;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringData;

class EncryptedMonitoringServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testMonitoring()
    {
        /** @var EncryptedMonitoringService $service */
        $service = $this->getMockBuilder(EncryptedMonitoringService::class)
            ->setMethods(['save', 'createMonitoringData'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $dataMock = $this->getMockForAbstractClass(DeliveryMonitoringData::class);
        $dataMock
            ->method('update')
            ->willReturn(true);
        $dataMock
            ->method('updateData')
            ->willReturn(true);

        $service
            ->method('createMonitoringData')
            ->willReturn($dataMock);

        $service
            ->method('save')
            ->willReturn(true);

        $service->executionCreated($this->mockEvent());

    }

    protected function mockEvent()
    {
        $event = $this->getMockBuilder(DeliveryExecutionCreated::class)->disableOriginalConstructor()->getMock();
        $event
            ->method('getDeliveryExecution')
            ->willReturn($this->mockDeliveryExecution())
        ;

        $event
            ->method('getUser')
            ->willReturn($this->mockUser())
        ;

        return $event;
    }

    protected function mockUser()
    {
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $user
            ->method('getPropertyValues')
            ->willReturn(['login']);

        return $user;

    }

    protected function mockDeliveryExecution()
    {
        $resource = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->setMethods(['getUri', 'getLabel'])->disableOriginalConstructor()->getMock();

        $deliveryExec = $this->getMockForAbstractClass(DeliveryExecutionInterface::class);
        $deliveryExec
            ->method('getState')
            ->willReturn($resource);
        $deliveryExec
            ->method('getUserIdentifier')
            ->willReturn('user');
        $deliveryExec
            ->method('getDelivery')
            ->willReturn($resource);
        $deliveryExec
            ->method('getStartTime')
            ->willReturn(time());
        $deliveryExec
            ->method('getIdentifier')
            ->willReturn('1231234');

        return $deliveryExec;
    }
}
