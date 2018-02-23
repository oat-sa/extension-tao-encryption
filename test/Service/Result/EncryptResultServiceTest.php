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
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoEncryption\Service\Result\EncryptResultService;
use PHPUnit\Framework\TestCase;
use taoResultServer_models_classes_Variable;
use Zend\ServiceManager\ServiceLocatorInterface;

class EncryptResultServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testStoreItems()
    {
        $variable = $this->getMockBuilder(taoResultServer_models_classes_Variable::class)->getMock();
        $service = $this->getService();

        $this->assertTrue($service->storeItemVariable(
            'deliveryResultIdentifier',
            'test',
            'item',
            $variable,
            'callIdItem'
        ));

        $this->assertTrue($service->storeItemVariables(
            'deliveryResultIdentifier',
            'test',
            'item',
            [$variable],
            'callIdItem'
        ));
    }

    /**
     * @throws \Exception
     */
    public function testStoreTests()
    {
        $variable = $this->getMockBuilder(taoResultServer_models_classes_Variable::class)->getMock();
        $service = $this->getService();

        $this->assertTrue($service->storeTestVariable(
            'deliveryResultIdentifier',
            'test',
            $variable,
            'callIdTest'
        ));

        $this->assertTrue($service->storeTestVariables(
            'deliveryResultIdentifier',
            'test',
            [$variable],
            'callIdTest'
        ));
    }

    private function getService()
    {
        $persistenceMock = $this->getMockBuilder(common_persistence_KeyValuePersistence::class)->disableOriginalConstructor()->getMock();
        $persistenceMock
            ->method('set')
            ->willReturn(true);
        $persistenceMock
            ->method('get')
            ->willReturn('');
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

        $encryption = $this->getMockBuilder(EncryptionServiceInterface::class)->getMock();
        $encryption
            ->method('encrypt')
            ->willReturn('encrypted');
        $encryption
            ->method('encrypt')
            ->willReturn('decrypted');

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $persistence,
                $encryption
            );


        $service = new EncryptResultService(array(
            EncryptResultService::OPTION_PERSISTENCE => 'encryptedResults',
            EncryptResultService::OPTION_ENCRYPTION_SERVICE => 'taoEncryption/asymmetricEncryptionService',
        ));
        $service->setServiceLocator($serviceLocator);

        return $service;
    }
}
