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
use oat\taoEncryption\Model\PrivateKey;
use oat\taoEncryption\Model\PublicKey;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;

class AsymmetricKeyPairProviderServiceTest extends TestCase
{
    public function testGetKeys()
    {
        $fileSystem = $this->getMockBuilder(FileSystem::class)->disableOriginalConstructor()->getMock();
        $fileSystem
            ->method('read')
            ->willReturn('key');

        $fileSystemService = $this->getMockBuilder(FileSystemService::class)->getMock();
        $fileSystemService
            ->method('getFileSystem')
            ->willReturn($fileSystem);

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $fileSystemService
            );

        $service = new AsymmetricKeyPairProviderService([
            AsymmetricKeyPairProviderService::OPTION_FILE_SYSTEM_ID => 'someFolder'
        ]);
        $service->setServiceLocator($serviceLocator);

        $this->assertInstanceOf(PublicKey::class, $service->getPublicKey());
        $this->assertInstanceOf(PrivateKey::class, $service->getPrivateKey());
    }

    public function testComparePublicKeyChecksum()
    {
        $service = new AsymmetricKeyPairProviderService([
            AsymmetricKeyPairProviderService::OPTION_FILE_SYSTEM_ID => 'someFolder'
        ]);

        $this->assertFalse($service->comparePublicKeyChecksum('bla', 'bla'));
    }
}
