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
namespace oat\taoEncryption\Test\Service\Sync;

use oat\taoEncryption\Rdf\EncryptedDeliveryRdf;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\Sync\EncryptRdfDeliverySyncFormatter;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;

class EncryptRdfDeliverySyncFormatterTest extends TestCase
{
    /**
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function testFilterProperties()
    {
        $service = $this->getService();

        $result = $service->filterProperties(['property1' => 'value1']);

        $this->assertIsArray( $result);
        $this->assertArrayHasKey(EncryptedDeliveryRdf::PROPERTY_APPLICATION_KEY, $result);
    }

    /**
     * @return EncryptRdfDeliverySyncFormatter
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(EncryptRdfDeliverySyncFormatter::class)->disableOriginalConstructor()
            ->setMethods(['callParentFilterProperties'])->getMockForAbstractClass();

        $service->setServiceLocator($this->mockServiceLocator());

        $service
            ->method('callParentFilterProperties')
            ->willReturn([
                'property1' => 'value1'
            ]);

        return $service;
    }

    protected function mockServiceLocator()
    {
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->mockFileKeyProvider()
            );

        return $serviceLocator;
    }

    protected function mockFileKeyProvider()
    {
        $service = $this->getMockBuilder(FileKeyProviderService::class)->getMock();

        $service
            ->method('getKeyFromFileSystem')
            ->willReturn('some key');

        return $service;
    }
}
