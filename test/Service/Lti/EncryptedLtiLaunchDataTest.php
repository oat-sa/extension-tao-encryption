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

namespace oat\taoEncryption\test\Service\Lti;

use oat\generis\test\TestCase;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchData;
use oat\taoLti\models\classes\LtiLaunchData;

class EncryptedLtiLaunchDataTest extends TestCase
{

    public function testEncryptedLaunchDataIsEncrypted()
    {
        $ltiLaunchData = $this->getMockBuilder(LtiLaunchData::class)->disableOriginalConstructor()->getMock();
        $ltiLaunchData
            ->method('getUserFamilyName')
            ->willReturn('family');
        $ltiLaunchData
            ->method('getUserGivenName')
            ->willReturn('given');
        $ltiLaunchData
            ->method('getUserFullName')
            ->willReturn('full');
        $ltiLaunchData
            ->method('getUserEmail')
            ->willReturn('email');
        $ltiLaunchData
            ->method('getUserID')
            ->willReturn('123');

        $encryptedLaunchData = new EncryptedLtiLaunchData($ltiLaunchData,'app_key');

        $encryptedLaunchData->setServiceLocator(
            $this->getServiceLocatorMock([
                SimpleKeyProviderService::SERVICE_ID => $this->getMockBuilder(SimpleKeyProviderService::class)->disableOriginalConstructor()->getMock(),
                AlgorithmSymmetricService::SERVICE_ID => $this->mockSymmetricService()
            ])
        );

        $encryptedString = base64_encode('encrypted_string');

        $this->assertSame($encryptedString, $encryptedLaunchData->getUserFamilyName());
        $this->assertSame($encryptedString, $encryptedLaunchData->getUserFullName());
        $this->assertSame($encryptedString, $encryptedLaunchData->getUserGivenName());
        $this->assertSame($encryptedString, $encryptedLaunchData->getUserEmail());
        $this->assertSame('123', $encryptedLaunchData->getUserID());
    }


    public function testEncryptedLaunchDataNotEncrypted()
    {
        $ltiLaunchData = $this->getMockBuilder(LtiLaunchData::class)->disableOriginalConstructor()->getMock();
        $ltiLaunchData
            ->method('getUserFamilyName')
            ->willReturn('family');
        $ltiLaunchData
            ->method('getUserGivenName')
            ->willReturn('given');
        $ltiLaunchData
            ->method('getUserFullName')
            ->willReturn('full');
        $ltiLaunchData
            ->method('getUserEmail')
            ->willReturn('email');
        $ltiLaunchData
            ->method('getUserID')
            ->willReturn('123');

        $encryptedLaunchData = new EncryptedLtiLaunchData($ltiLaunchData,'app_key', false);

        $encryptedLaunchData->setServiceLocator(
            $this->getServiceLocatorMock([
                SimpleKeyProviderService::SERVICE_ID => $this->getMockBuilder(SimpleKeyProviderService::class)->disableOriginalConstructor()->getMock(),
                AlgorithmSymmetricService::SERVICE_ID => $this->mockSymmetricService()
            ])
        );

        $this->assertSame('family', $encryptedLaunchData->getUserFamilyName());
        $this->assertSame('full', $encryptedLaunchData->getUserFullName());
        $this->assertSame('given', $encryptedLaunchData->getUserGivenName());
        $this->assertSame('email', $encryptedLaunchData->getUserEmail());
        $this->assertSame('123', $encryptedLaunchData->getUserID());
    }

    protected function mockSymmetricService()
    {
        $service = $this->getMockBuilder(AlgorithmSymmetricService::class)->disableOriginalConstructor()->getMock();

        $service->method('encrypt')->willReturn('encrypted_string');

        return $service;
    }
}
