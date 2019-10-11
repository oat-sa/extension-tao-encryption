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

namespace oat\taoEncryption\Test\Service\SessionState;

use common_persistence_KeyValuePersistence;
use core_kernel_users_GenerisUser;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Session\EncryptedUser;
use oat\taoEncryption\Service\SessionState\EncryptedStateStorage;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;
use oat\generis\test\MockObject;

class EncryptedStateStorageTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testEncryptedStateSet()
    {
        $state = $this->getService();

        $this->assertTrue($state->set('user id', 'call id', 'some secret data'));
    }

    /**
     * @throws \Exception
     */
    public function testEncryptedStateGet()
    {
        $state = $this->getService();

        $this->assertEquals('some secret data', $state->get('call id', 'some secret data'));
    }

    /**
     * @expectedException \Exception
     */
    public function testUserNotEncrypted()
    {
        $state = $this->getService(false);

        $state->get('call id', 'some secret data');
    }


    /**
     * @param bool $encrypted
     * @return MockObject
     */
    public function getService($encrypted = true)
    {
        $service = $this->getMockBuilder(EncryptedStateStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getPersistence'])
            ->getMockForAbstractClass();

        $service
            ->method('getUser')
            ->willReturn($this->mockUser($encrypted));

        $service
            ->method('getPersistence')
            ->willReturn($this->mockPersistence());

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls($this->mockEncryptionService(), $this->mockEncryptionKeyProvider());
        $service->setServiceLocator($serviceLocator);

        return $service;
    }

    protected function mockUser($encrypted = true)
    {
        if ($encrypted){
            $encryptUser = $this->getMockBuilder(EncryptedUser::class)->disableOriginalConstructor()->getMock();
            $encryptUser
                ->method('getApplicationKey')
                ->willReturn('application key');

            return $encryptUser;
        }

        return $this->getMockBuilder(core_kernel_users_GenerisUser::class)->disableOriginalConstructor()->getMock();
    }

    protected function mockPersistence()
    {
        $persistenceMock = $this->getMockBuilder(common_persistence_KeyValuePersistence::class)->disableOriginalConstructor()->getMock();
        $persistenceMock
            ->method('set')
            ->willReturn(true);
        $persistenceMock
            ->method('get')
            ->willReturnOnConsecutiveCalls('some secret data');
        $persistenceMock
            ->method('exists')
            ->willReturn(true);
        $persistenceMock
            ->method('del')
            ->willReturn(true);

        return $persistenceMock;
    }

    protected function mockEncryptionService()
    {
        $service = $this->getMockBuilder(EncryptionSymmetricService::class)->getMock();

        $service
            ->method('decrypt')
            ->willReturn('some secret data');

        return $service;
    }

    protected function mockEncryptionKeyProvider()
    {
        $service = $this->getMockBuilder(SimpleKeyProviderService::class)->getMock();

        return $service;
    }
}
