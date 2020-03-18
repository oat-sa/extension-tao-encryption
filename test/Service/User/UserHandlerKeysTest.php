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
namespace oat\taoEncryption\Test\Service\User;


use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\User\UserHandlerKeys;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;

class UserHandlerKeysTest extends TestCase
{

    public function testGenerateUserKey()
    {
        $handler = new UserHandlerKeys();

        $this->assertIsString($handler->generateUserKey('hash', 'salt'));
    }

    /**
     * @throws \Exception
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function testEncryptApplicationKey()
    {
        $handler = new UserHandlerKeys();

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->mockEncryptionService(),
                $this->mockEncryptionKeyProvider(),
                $this->mockFileKeyProvider()
            );
        $handler->setServiceLocator($serviceLocator);

        $this->assertIsString($handler->encryptApplicationKey('key to encrypt'));
    }

    protected function mockEncryptionService()
    {
        $service = $this->getMockBuilder(EncryptionSymmetricService::class)->getMock();

        $service
            ->method('decrypt')
            ->willReturn('some secret data');

        return $service;
    }

    protected function mockEncryptionKeyProvider()
    {
        $service = $this->getMockBuilder(SimpleKeyProviderService::class)->getMock();

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
