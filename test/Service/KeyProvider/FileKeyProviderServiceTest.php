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
namespace oat\taoEncryption\Test\Service\KeyProvider;


use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoEncryption\Model\Key;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\Session\EncryptedUser;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileKeyProviderServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testGetKey()
    {
        $service = $this->getService();

        $service
            ->method('callParentGetKey')
            ->willReturnOnConsecutiveCalls(
                null, new Key('')
            );

        $this->assertInstanceOf(Key::class, $service->getKey());
    }

    public function testGetKeySetByParent()
    {
        $service = $this->getService();

        $service
            ->method('callParentGetKey')
            ->willReturn(new Key('key'));

        $this->assertInstanceOf(Key::class, $service->getKey());
    }

    public function testGetKeyFromUserSession()
    {
        $service = $this->getService();
        $service
            ->method('callParentGetKey')
            ->willReturnOnConsecutiveCalls(
                null, new Key('key')
            );

        $encryptedUser = $this->getMockBuilder(EncryptedUser::class)->disableOriginalConstructor()->getMock();
        $encryptedUser
            ->method('getApplicationKey')
            ->willReturn('key');

        $service
            ->method('getSessionUser')
            ->willReturn($encryptedUser);

        $this->assertInstanceOf(Key::class, $service->getKey());
    }

    public function testGenerateAndSaveKey()
    {
        $fileSystem = $this->getMockBuilder(FileSystem::class)->disableOriginalConstructor()->getMock();
        $fileSystem
            ->method('put')
            ->willReturn(true);

        $fileSystemService = $this->getMockBuilder(FileSystemService::class)->getMock();
        $fileSystemService
            ->method('getFileSystem')
            ->willReturn($fileSystem);

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $fileSystemService
            );

        $service = $this->getService();

        $service->setServiceLocator($serviceLocator);

        $this->assertTrue($service->generateAndSaveKey());
    }

    public function testGetKeyFromFileSystem()
    {
        $fileSystem = $this->getMockBuilder(FileSystem::class)->disableOriginalConstructor()->getMock();
        $fileSystem
            ->method('read')
            ->willReturn('some key');

        $fileSystemService = $this->getMockBuilder(FileSystemService::class)->getMock();
        $fileSystemService
            ->method('getFileSystem')
            ->willReturn($fileSystem);

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $fileSystemService
            );

        $service = $this->getService();

        $service->setServiceLocator($serviceLocator);

        $this->assertInternalType('string', $service->getKeyFromFileSystem());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(FileKeyProviderService::class)->disableOriginalConstructor()
            ->setMethods(['callParentGetKey', 'getSessionUser'])
            ->getMockForAbstractClass();


        return $service;
    }
}
