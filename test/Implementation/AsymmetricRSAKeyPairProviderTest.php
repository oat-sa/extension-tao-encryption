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
namespace oat\Encryption\Test\Implementation;

use League\Flysystem\FilesystemInterface;
use oat\Encryption\Implementation\AsymmetricRSAKeyPairProvider;
use oat\Encryption\Model\KeyPairEncryption;
use oat\Encryption\Model\PrivateKey;
use oat\Encryption\Model\PublicKey;
use PHPUnit\Framework\TestCase;

class AsymmetricRSAKeyPairProviderTest extends TestCase
{
    public function testGenerateKeyPair()
    {
        $fileSystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $provider = new AsymmetricRSAKeyPairProvider($fileSystem);

        $this->assertInstanceOf(KeyPairEncryption::class, $provider->generate());
    }

    public function testSavePublicKey()
    {
        $fileSystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $fileSystem
            ->method('put')
            ->willReturn(true);

        $provider = new AsymmetricRSAKeyPairProvider($fileSystem);
        $keyPair  = $provider->generate();

        $this->assertTrue($provider->savePublicKey($keyPair->getPublicKey()));
    }

    public function testGetPublicKey()
    {
        $fileSystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $fileSystem
            ->method('read')
            ->willReturn('public key');

        $provider = new AsymmetricRSAKeyPairProvider($fileSystem);

        $this->assertInstanceOf(PublicKey::class, $provider->getPublicKey());
        $this->assertSame('public key', $provider->getPublicKey()->getKey());
    }

    public function testSavePrivateKey()
    {
        $fileSystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $fileSystem
            ->method('put')
            ->willReturn(true);

        $provider = new AsymmetricRSAKeyPairProvider($fileSystem);
        $keyPair  = $provider->generate();

        $this->assertTrue($provider->savePrivateKey($keyPair->getPrivateKey()));
    }

    public function testGetPrivateKey()
    {
        $fileSystem = $this->getMockBuilder(FilesystemInterface::class)->getMock();
        $fileSystem
            ->method('read')
            ->willReturn('private key');

        $provider = new AsymmetricRSAKeyPairProvider($fileSystem);

        $this->assertInstanceOf(PrivateKey::class, $provider->getPrivateKey());
        $this->assertSame('private key', $provider->getPrivateKey()->getKey());
    }
}
