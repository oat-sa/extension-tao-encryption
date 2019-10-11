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
namespace oat\taoEncryption\Test\Service;

use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SymmetricKeyProviderService;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;

class EncryptionSymmetricServiceTest extends TestCase
{
    public function testFlow()
    {
        $algorithm = $this->getMockBuilder(AlgorithmSymmetricService::class)
            ->setMethods(['encrypt','decrypt'])->disableOriginalConstructor()->getMock();
        $algorithm
            ->method('encrypt')
            ->willReturn('encryptString');
        $algorithm
            ->method('decrypt')
            ->willReturn('decryptString');


        $keyProvider = $this->getMockBuilder(SymmetricKeyProviderService::class)->disableOriginalConstructor()->getMock();
        $keyProvider
            ->method('getKey')
            ->willReturn('key');

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $algorithm
            );

        $service = new EncryptionSymmetricService([
            EncryptionSymmetricService::OPTION_ENCRYPTION_ALGORITHM => 'taoEncryption/symmetricAlgorithm',
        ]);
        $service->setServiceLocator($serviceLocator);
        $service->setKeyProvider($keyProvider);

        $this->assertEquals('encryptString', $service->encrypt('encryptString'));
        $this->assertEquals('decryptString', $service->decrypt('encryptString'));
    }
}
