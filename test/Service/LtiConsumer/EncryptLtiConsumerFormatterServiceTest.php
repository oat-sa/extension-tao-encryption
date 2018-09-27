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
namespace oat\taoEncryption\Test\Service\LtiConsumer;

use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\LtiConsumer\EncryptedLtiConsumer;
use oat\taoEncryption\Service\LtiConsumer\EncryptLtiConsumerFormatterService;
use Zend\ServiceManager\ServiceLocatorInterface;

class EncryptLtiConsumerFormatterServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     * @throws \Exception
     */
    public function testFilterPropertiesEncryptsAppKey()
    {
        $service = $this->getService();

        $result = $service->filterProperties(['property1' => 'value1']);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey(EncryptedLtiConsumer::PROPERTY_ENCRYPTED_APPLICATION_KEY, $result);
        $this->assertEquals(base64_encode('encryptContent'), $result[EncryptedLtiConsumer::PROPERTY_ENCRYPTED_APPLICATION_KEY]);
    }

    /**
     * @return EncryptLtiConsumerFormatterService
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(EncryptLtiConsumerFormatterService::class)->disableOriginalConstructor()
            ->setMethods(['callParentFilterProperties'])->getMockForAbstractClass();

        $service->setServiceLocator($this->mockServiceLocator());

        $service
            ->method('callParentFilterProperties')
            ->willReturn([
                EncryptedLtiConsumer::PROPERTY_CUSTOMER_APP_KEY => 'CustomerAppKey'
            ]);

        return $service;
    }

    protected function mockServiceLocator()
    {
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->mockSimpleKeyProvider(),
                $this->mockEncryptionService(),
                $this->mockFileKeyProvider()
            );

        return $serviceLocator;
    }

    protected function mockEncryptionService()
    {
        $service = $this->getMockBuilder(EncryptionSymmetricService::class)->disableOriginalConstructor()->getMock();

        $service
            ->method('encrypt')
            ->willReturn('encryptContent');

        return $service;
    }

    protected function mockSimpleKeyProvider()
    {
        $service = $this->getMockBuilder(SimpleKeyProviderService::class)->getMock();

        $service
            ->method('setKey')
            ->willReturn(true);

        return $service;
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
