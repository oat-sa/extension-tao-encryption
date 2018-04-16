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


use oat\taoEncryption\Service\KeyProvider\KeyProviderClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class KeyProviderClientTest extends \PHPUnit_Framework_TestCase
{

    public function testUpdatePublicKey()
    {
        $service = $this->getMockBuilder(KeyProviderClient::class)->disableOriginalConstructor()
            ->setMethods(['call'])
            ->getMockForAbstractClass();

        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn('200');
        $response
            ->method('getBody')
            ->willReturn($this->getMockForAbstractClass(StreamInterface::class));

        $service
            ->method('call')
            ->willReturn($response);

        $this->assertInstanceOf(StreamInterface::class, $service->updatePublicKey('some key'));
    }

    /**
     * @expectedException \common_Exception
     */
    public function testUpdatePublicKeyFailed()
    {
        $service = $this->getMockBuilder(KeyProviderClient::class)->disableOriginalConstructor()
            ->setMethods(['call', 'logError'])
            ->getMockForAbstractClass();

        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn('400');
        $response
            ->method('getBody')
            ->willReturn($this->getMockForAbstractClass(StreamInterface::class));

        $service
            ->method('call')
            ->willReturn($response);


        $this->assertInstanceOf(StreamInterface::class, $service->updatePublicKey('some key'));
    }
}
