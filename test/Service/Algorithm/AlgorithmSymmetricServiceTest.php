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
namespace oat\taoEncryption\Test\Service\Algorithm;

use oat\taoEncryption\Model\Key;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SymmetricKeyProviderService;
use PHPUnit\Framework\TestCase;

class AlgorithmSymmetricServiceTest extends TestCase
{
    public function testEncryptAndDecrypt()
    {
        $keyProvider = $this->getMockBuilder(SymmetricKeyProviderService::class)->disableOriginalConstructor()->getMock();
        $keyProvider
            ->method('getKey')
            ->willReturn(new Key('secretkey'));

        $service = new AlgorithmSymmetricService([
            AlgorithmSymmetricService::OPTION_ALGORITHM => 'RC4'
        ]);

        $service->setKeyProvider($keyProvider);

        $encrypted = $service->encrypt('secret');
        $this->assertInternalType('string', $encrypted);
        $this->assertEquals('secret', $service->decrypt($encrypted));
    }
}
