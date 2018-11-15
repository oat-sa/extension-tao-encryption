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

use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Lti\EncryptLtiUserFactoryService;
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchDataStorage;
use oat\taoLti\models\classes\LtiLaunchData;
use oat\taoLti\models\classes\user\LtiUserInterface;

class EncryptLtiUserFactoryServiceTest extends TestCase
{
    public function testCreate()
    {
        $ltiUserFactory = new EncryptLtiUserFactoryService();
        $ltiUserFactory->setOption(EncryptLtiUserFactoryService::OPTION_LAUNCH_DATA_STORAGE, 'launch_data_storage');

        $launchData = $this->getMockBuilder(LtiLaunchData::class)->disableOriginalConstructor()->getMock();
        $launchData
            ->method('getLtiConsumer')
            ->willReturn($this->mockLtiConsumer());
        $launchData
            ->method('hasVariable')
            ->willReturnOnConsecutiveCalls(true, false);
        $launchData
            ->method('getVariable')
            ->willReturn('customer_app_key');


        $ltiUserFactory->setServiceLocator(
            $this->getServiceLocatorMock([
                'launch_data_storage' => $this->getMockBuilder(EncryptedLtiLaunchDataStorage::class)->disableOriginalConstructor()->getMock(),
                EncryptionSymmetricService::SERVICE_ID => $this->mockEncryptService(),
                SimpleKeyProviderService::SERVICE_ID => $this->getMockBuilder(SimpleKeyProviderService::class)->disableOriginalConstructor()->getMock(),
                AlgorithmSymmetricService::SERVICE_ID => $this->getMockBuilder(AlgorithmSymmetricService::class)->disableOriginalConstructor()->getMock()
            ])
        );

        $this->assertInstanceOf(LtiUserInterface::class, $ltiUserFactory->create($launchData, '123'));
    }

    protected function mockLtiConsumer()
    {
        $resource = $this->getMockBuilder(core_kernel_classes_Resource::class)->disableOriginalConstructor()->getMock();

        $stdClass = new \stdClass();
        $stdClass->literal = 'app_key_secret';

        $resource
            ->method('getUniquePropertyValue')
            ->willReturn($stdClass);

        return $resource;
    }

    protected function mockEncryptService()
    {
        $encrypt = $this->getMockBuilder(EncryptionSymmetricService::class)->disableOriginalConstructor()->getMock();

        $encrypt->method('decrypt')->willReturn('decrypted_key');

        return $encrypt;
    }
}
