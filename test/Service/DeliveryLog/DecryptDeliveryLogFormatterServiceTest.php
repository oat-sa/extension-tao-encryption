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
namespace oat\taoEncryption\Test\Service\DeliveryLog;

use oat\taoEncryption\Service\DeliveryLog\DecryptDeliveryLogFormatterService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\generis\test\TestCase;

class DecryptDeliveryLogFormatterServiceTest extends TestCase
{
    public function testFormat()
    {
        $service = $this->getService();

        $this->assertIsArray( $service->format([
            'data' => 'something encrypted'
        ])) ;
    }

    /**
     * @return DecryptDeliveryLogFormatterService
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(DecryptDeliveryLogFormatterService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApplicationKey', 'getEncryptionService'])
            ->getMockForAbstractClass();

        $service
            ->method('getApplicationKey')
            ->willReturn('application key');

        $service
            ->method('getEncryptionService')
            ->willReturn($this->mockEncryptionService());

        return $service;
    }

    protected function mockEncryptionService()
    {
        $encryption = $this->getMockBuilder(EncryptionSymmetricService::class)->getMock();
        $encryption
            ->method('encrypt')
            ->willReturn('encrypted');
        $encryption
            ->method('decrypt')
            ->willReturn('decrypted');

        return $encryption;
    }
}
