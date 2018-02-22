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

use oat\taoEncryption\Service\Algorithm\AlgorithmAsymmetricRSAService;
use oat\taoEncryption\Service\EncryptionAsymmetricService;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;

class EncryptionAsymmetricServiceTest extends TestCase
{
    public function testFlow()
    {
        $mockRsaAlgo = $this->getMockBuilder(AlgorithmAsymmetricRSAService::class)->disableOriginalConstructor()->getMock();
        $mockRsaAlgo
            ->method('encrypt')
            ->willReturn('encryptString');
        $mockRsaAlgo
            ->method('decrypt')
            ->willReturn('decryptString');
        $mockRsaAlgo
            ->method('setKeyPairProvider')
            ->willReturn(null);

        $keyPairProvider = $this->getMockBuilder(AsymmetricKeyPairProviderService::class)->disableOriginalConstructor()->getMock();
        $keyPairProvider
            ->method('getPublicKey')
            ->willReturn('publicKey');
        $keyPairProvider
            ->method('getPrivateKey')
            ->willReturn('privateKey');

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls(
                $mockRsaAlgo,
                $keyPairProvider
            );

        $asymmetricService = new EncryptionAsymmetricService([
            EncryptionAsymmetricService::OPTION_ENCRYPTION_ALGORITHM => 'taoEncryption/asymmetricAlgorithm',
            EncryptionAsymmetricService::OPTION_KEY_PAIR_PROVIDER => 'taoEncryption/asymmetricKeyPairProvider'
        ]);

        $asymmetricService->setServiceLocator($serviceLocator);

        $this->assertEquals('encryptString', $asymmetricService->encrypt('encryptString'));
        $this->assertEquals('decryptString', $asymmetricService->decrypt('encryptString'));
    }
}
