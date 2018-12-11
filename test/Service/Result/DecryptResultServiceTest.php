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
use oat\generis\test\TestCase;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoEncryption\Service\Mapper\MapperClientUserIdToCentralUserIdInterface;
use oat\taoEncryption\Service\Mapper\TestSessionSyncMapper;
use oat\taoEncryption\Service\Result\DecryptResultService;
use oat\taoEncryption\Service\Result\StoreVariableServiceInterface;
use oat\taoEncryption\Service\Result\SyncEncryptedResultService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\Entity\ItemVariableStorable;
use oat\taoResultServer\models\Entity\TestVariableStorable;
use oat\taoSync\model\TestSession\SyncTestSessionServiceInterface;
use taoResultServer_models_classes_Variable;
use taoResultServer_models_classes_WritableResultStorage;

class DecryptResultServiceTest extends TestCase
{

    public function testDecrypt()
    {
        $service = $this->getService();

        $service->setServiceLocator($this->mockServiceLocator());

        $this->assertInstanceOf(\common_report_Report::class, $service->decrypt('delivery id'));
    }

    /**
     * @return DecryptResultService
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(DecryptResultService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResultRow', 'getDeliveryExecution', 'getStoreVariableService', 'getUserIdClientToUserIdCentralMapper'])
            ->getMockForAbstractClass()
        ;

        $itemVariable = $this->getMockBuilder(ItemVariableStorable::class)->disableOriginalConstructor()->getMock();
        $itemVariable
            ->method('getVariable')
            ->willReturn($this->getMockForAbstractClass(taoResultServer_models_classes_Variable::class));

        $testVariable = $this->getMockBuilder(TestVariableStorable::class)->disableOriginalConstructor()->getMock();
        $testVariable
            ->method('getVariable')
            ->willReturn($this->getMockForAbstractClass(taoResultServer_models_classes_Variable::class));

        $service
            ->method('getResultRow')
            ->willReturnOnConsecutiveCalls(
                $itemVariable,
                $testVariable
            );

        $service
            ->method('getDeliveryExecution')
            ->willReturn($this->mockDeliveryExecution());

        $storeVariableService = $this->getMockForAbstractClass(StoreVariableServiceInterface::class);
        $storeVariableService
            ->method('save')
            ->willReturn(true);

        $service
            ->method('getStoreVariableService')
            ->willReturn($storeVariableService);

        $service
            ->method('getUserIdClientToUserIdCentralMapper')
            ->willReturn($this->getMockForAbstractClass(MapperClientUserIdToCentralUserIdInterface::class));

        $service->setOption(DecryptResultService::OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL, MapperClientUserIdToCentralUserIdInterface::class);
        $service->setOption(DecryptResultService::OPTION_ENCRYPTION_SERVICE, EncryptionServiceInterface::class);
        $service->setOption(DecryptResultService::OPTION_REMOVE_VARIABLE_AFTER_DECRYPT, true);

        return $service;
    }

    protected function mockServiceLocator()
    {
        $serviceLocator = $this->getServiceLocatorMock([
            MapperClientUserIdToCentralUserIdInterface::class => $this->mockMapperClientToServer(),
            ResultServerService::SERVICE_ID => $this->mockResultService(),
            common_persistence_Manager::SERVICE_ID => $this->mockPersistence(),
            EncryptionServiceInterface::class =>$this->mockEncryptionService(),
            TestSessionSyncMapper::SERVICE_ID => $this->mockTestSessionSyncMapper(),
            SyncTestSessionServiceInterface::SERVICE_ID => $this->mockSyncTestSessionService(),
            SyncEncryptedResultService::SERVICE_ID => $this->mockSyncEncryptedResultService()
        ]);

        return $serviceLocator;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockSyncEncryptedResultService()
    {
        $service = $this->getMockBuilder(SyncEncryptedResultService::class)->disableOriginalConstructor()->getMock();

        $service
            ->method('deleteVariable')
            ->willReturn(true);

        return $service;
    }

    /**
     * @return MapperClientUserIdToCentralUserIdInterface
     */
    protected function mockMapperClientToServer()
    {
        return $this->getMockForAbstractClass(MapperClientUserIdToCentralUserIdInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockDeliveryExecution()
    {
        $deliveryExec = $this->getMockBuilder(DeliveryExecution::class)->disableOriginalConstructor()->getMock();

        return $deliveryExec;
    }

    protected function mockResultService()
    {
        $service = $this->getMockForAbstractClass(ResultServerService::class);
        $service
            ->method('getResultStorage')
            ->willReturn($this->getMockForAbstractClass(taoResultServer_models_classes_WritableResultStorage::class));

        return $service;
    }

    protected function mockEncryptionService()
    {
        $service = $this->getMockForAbstractClass(EncryptionServiceInterface::class);
        $service
            ->method('decrypt')
            ->willReturnOnConsecutiveCalls(
                json_encode([
                    'deliveryIdentifier' => 'delivery identifier 1',
                    'testTakerIdentifier' => 'test taker identifier 1',
                ]),
                json_encode([
                    'deliveryResultIdentifier' => 'delivery result identifier 1',
                    'deliveryIdentifier' => 'delivery identifier 1',
                ])
            );

        return $service;
    }

    /**
     * @return TestSessionSyncMapper
     */
    protected function mockTestSessionSyncMapper()
    {
        $service = $this->getMockBuilder(TestSessionSyncMapper::class)->getMock();

        $service
            ->method('get')
            ->willReturn('some id');

        $service
            ->method('delete')
            ->willReturn(true);

        return $service;
    }

    /**
     * @return SyncTestSessionServiceInterface
     */
    protected function mockSyncTestSessionService()
    {
        $service = $this->getMockForAbstractClass(SyncTestSessionServiceInterface::class);

        $service
            ->method('touchTestSession')
            ->willReturn(true);

        return $service;
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
            ->willReturnOnConsecutiveCalls(
                json_encode([
                    'result_id_1',
                ]),
                json_encode([
                    'deliveryIdentifier' => 'delivery identifier 1',
                    'testTakerIdentifier' => 'test taker identifier 1',
                ]),
                json_encode([
                    'deliveryResultIdentifier' => 'delivery result identifier 1',
                    'deliveryIdentifier' => 'delivery identifier 1',
                ]),
                json_encode([
                    'reference of item',
                    'reference of test',
                ]),
                json_encode([
                    'result_id_1',
                ]),
                json_encode([
                    'result_id_1',
                ])
            );
        $persistenceMock
            ->method('exists')
            ->willReturn(true);
        $persistenceMock
            ->method('del')
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
}
