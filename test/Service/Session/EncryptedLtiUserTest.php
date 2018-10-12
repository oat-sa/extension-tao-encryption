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
namespace oat\taoEncryption\Test\Service\Session;

use core_kernel_classes_Literal;
use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Session\EncryptedLtiUser;
use oat\taoLti\models\classes\LtiLaunchData;
use Zend\ServiceManager\ServiceLocatorInterface;

class EncryptedLtiUserTest extends TestCase
{

    public function testGetApplicationKey()
    {
        $encryptLtiUser = $this->mockEncryptLtiUser();

        $this->assertSame('application_key_decrypted', $encryptLtiUser->getApplicationKey());
    }

    /**
     * @return EncryptedLtiUser
     */
    public function mockEncryptLtiUser()
    {
        $user = $this->getMockBuilder(EncryptedLtiUser::class)->disableOriginalConstructor()
            ->setMethods(['getLaunchData','getServiceLocator'])
            ->getMockForAbstractClass();

        $user
            ->method('getLaunchData')
            ->willReturn($this->mockLaunchData());
        $user
            ->method('getServiceLocator')
            ->willReturn($this->mockServiceLocator());

        return $user;
    }

    protected function mockLaunchData()
    {
        $data = $this->getMockBuilder(LtiLaunchData::class)->disableOriginalConstructor()->getMock();
        $data
            ->method('getLtiConsumer')
            ->willReturn($this->mockLtiConsumer());

        $data
            ->method('getVariables')
            ->willReturn([
                'custom_customer_app_key' => 'customer_app_key'
            ]);

        return $data;
    }

    protected function mockLtiConsumer()
    {
        $mock = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->setMethods(['getUniquePropertyValue'])
            ->disableOriginalConstructor()->getMock();

        $literal = $this->getMockBuilder(core_kernel_classes_Literal::class)->disableOriginalConstructor()->getMock();
        $literal->value = 'APP KEY ENCRYPTED';

        $mock->method('getUniquePropertyValue')->willReturn($literal);

        return $mock;
    }

    protected function mockServiceLocator()
    {
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($prop){
                switch ($prop){
                    case EncryptionSymmetricService::SERVICE_ID:
                        return $this->mockEncryptionService();
                    case SimpleKeyProviderService::SERVICE_ID:
                        return $this->mockEncryptionKeyProvider();
                }
            }));

        return $serviceLocator;
    }

    protected function mockEncryptionService()
    {
        $service = $this->getMockBuilder(EncryptionSymmetricService::class)->getMock();

        $service
            ->method('decrypt')
            ->willReturn('application_key_decrypted');

        return $service;
    }

    protected function mockEncryptionKeyProvider()
    {
        $service = $this->getMockBuilder(SimpleKeyProviderService::class)->getMock();

        return $service;
    }
}
