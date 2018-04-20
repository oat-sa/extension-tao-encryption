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
namespace oat\taoEncryption\Test\Service\User;


use core_kernel_classes_Resource;
use oat\taoEncryption\Service\Session\EncryptedUser;
use oat\taoEncryption\Service\User\EncryptedUserFactoryService;

class EncryptedUserFactoryServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @throws \Exception
     */
    public function testCreateUser()
    {
        $factory = new EncryptedUserFactoryService([
            EncryptedUserFactoryService::OPTION_USER_CLASS_WRAPPED => 'core_kernel_users_GenerisUser'
        ]);

        $this->assertInstanceOf(EncryptedUser::class, $factory->createUser(
            $this->mockResource(),
            'some hash'
        ));
    }

    /**
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testCreateUserWithWrongClassFailed()
    {
        $factory = new EncryptedUserFactoryService([
            EncryptedUserFactoryService::OPTION_USER_CLASS_WRAPPED => 'core_kernel_users_Exception'
        ]);

        $this->assertInstanceOf(EncryptedUser::class, $factory->createUser(
            $this->mockResource(),
            'some hash'
        ));
    }

    /**
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testCreateUserWithNonExistingClass()
    {
        $factory = new EncryptedUserFactoryService([
            EncryptedUserFactoryService::OPTION_USER_CLASS_WRAPPED => 'some non existing class'
        ]);

        $this->assertInstanceOf(EncryptedUser::class, $factory->createUser(
            $this->mockResource(),
            'some hash'
        ));
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockResource()
    {
        $mock = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->setMethods(['getUri'])
            ->disableOriginalConstructor()->getMock();

        $mock
            ->method('getUri')
            ->willReturn('some uri');

        return $mock;
    }
}
