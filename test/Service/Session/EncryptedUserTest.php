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


use common_user_User;
use oat\taoEncryption\Service\Session\EncryptedUser;

class EncryptedUserTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructingEncryptedUser()
    {
        $encryptUser = new EncryptedUser($this->mockCommonUser('user identifier'), 'some hash');

        $this->assertSame('user identifier', $encryptUser->getIdentifier());
        $this->assertInternalType('string', $encryptUser->getKey());
        $this->assertInternalType('string', $encryptUser->getApplicationKey());
        $this->assertInternalType('string', serialize($encryptUser));
    }

    /**
     * @return common_user_User
     */
    protected function mockCommonUser($userIdentifier)
    {
        $commonUser = $this->getMockForAbstractClass(common_user_User::class);
        $commonUser
            ->method('getPropertyValues')
            ->willReturnOnConsecutiveCalls(
                [
                    'password',
                ],
                [
                    'application key',
                ]
            );

        $commonUser
            ->method('getIdentifier')
            ->willReturn($userIdentifier);

        return $commonUser;
    }
}
