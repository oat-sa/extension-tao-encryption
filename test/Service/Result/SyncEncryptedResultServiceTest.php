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
namespace oat\taoEncryption\Test\Service\Result;

use common_persistence_KeyValuePersistence;
use common_persistence_Manager;
use core_kernel_classes_Resource;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoEncryption\Service\Mapper\MapperClientUserIdToCentralUserIdInterface;
use oat\taoEncryption\Service\Result\SyncEncryptedResultService;
use Zend\ServiceManager\ServiceLocatorInterface;

class SyncEncryptedResultServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testImportDeliveryResults()
    {
        $service = $this->getService();

        $result = $service->importDeliveryResults([
            'result_id' => [
                'deliveryId' => 'delivery id',
                'deliveryExecutionId' => 'delivery execution id',
                'details' => [
                    'identifier' => 'identifier',
                    'label' => 'label',
                    'starttime' => 'starttime',
                    'finishtime' => 'finishtime',
                    'state' => 'state',
                    'test-taker' => 'taker id',
                ],
                'variables' => [
                    [], []
                ]
            ]
        ]);

        $this->assertInternalType('array', $result);
        $this->assertEquals([
            'result_id' => [
                'success' => 1,
                'deliveryId' => 'delivery id',
            ]
        ], $result);
    }

    public function testImportFailed()
    {
        $service = $this->getService();
        $result = $service->importDeliveryResults([
            'result_id' => [
                'deliveryId' => 'delivery id',
            ]
        ]);

        $this->assertInternalType('array', $result);
        $this->assertEquals([
            'result_id' => [
                'success' => 0,
            ]
        ], $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockResource()
    {
        $mock = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->setMethods(['getUri'])
            ->disableOriginalConstructor()->getMock();

        return $mock;
    }


    /**
     * @return SyncEncryptedResultService
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(SyncEncryptedResultService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResource', 'getUserIdClientToUserIdCentralMapper', 'dispatchDecryptTask', 'spawnDeliveryExecution', 'updateDeliveryExecution', 'mapOfflineResultIdToOnlineResultId'])
            ->getMockForAbstractClass();

        $service
            ->method('getResource')
            ->willReturn($this->mockResource());

        $service
            ->method('spawnDeliveryExecution')
            ->willReturn($this->mockDeliveryExecution());

        $service
            ->method('updateDeliveryExecution')
            ->willReturn($this->mockDeliveryExecution());

        $service
            ->method('mapOfflineResultIdToOnlineResultId')
            ->willReturn(true);

        $service
            ->method('dispatchDecryptTask')
            ->willReturn(true);
        $service
            ->method('getUserIdClientToUserIdCentralMapper')
            ->willReturn($this->getMockForAbstractClass(MapperClientUserIdToCentralUserIdInterface::class));


        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls($this->mockPersistence(), $this->mockEncryptionService());
        $service->setServiceLocator($serviceLocator);

        return $service;
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockPersistence()
    {
        $persistenceMock = $this->getMockBuilder(common_persistence_KeyValuePersistence::class)->disableOriginalConstructor()->getMock();
        $persistenceMock
            ->method('set')
            ->willReturn(true);
        $persistenceMock
            ->method('get')
            ->willReturnOnConsecutiveCalls(null);
        $persistenceMock
            ->method('exists')
            ->willReturn(true);
        $persistenceMock
            ->method('del')
            ->willReturn(true);

        $persistence = $this->getMockBuilder(common_persistence_Manager::class)->getMock();
        $persistence
            ->method('getPersistenceById')
            ->willReturn($persistenceMock);

        return $persistence;
    }

    protected function mockEncryptionService()
    {
        $encryption = $this->getMockBuilder(EncryptionServiceInterface::class)->getMock();
        $encryption
            ->method('encrypt')
            ->willReturn('encrypted');
        $encryption
            ->method('encrypt')
            ->willReturn('decrypted');

        return $encryption;
    }
}
