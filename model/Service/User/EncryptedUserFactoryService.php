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
namespace oat\taoEncryption\Service\User;

use core_kernel_classes_Resource;
use oat\generis\model\user\UserFactoryServiceInterface;
use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\Service\Session\EncryptedUser;

class EncryptedUserFactoryService extends ConfigurableService implements UserFactoryServiceInterface
{
    const OPTION_USER_CLASS_WRAPPED = 'userClassToBeWrapped';

    /**
     * @param core_kernel_classes_Resource $userResource
     * @param string $hashForEncryption
     * @return \common_user_User
     * @throws \Exception
     */
    public function createUser(core_kernel_classes_Resource $userResource, $hashForEncryption = null)
    {
        $class = $this->getOption(static::OPTION_USER_CLASS_WRAPPED);
        if (!class_exists($class)){
            throw  new \Exception('Incorrect user class provided.');
        }
        $userWrapped = new $class($userResource);
        if (!$userWrapped instanceof \common_user_User){
            throw  new \Exception('Incorrect user class provided.');
        }

        $user = new EncryptedUser($userWrapped, $hashForEncryption);

        $this->propagate($user);

        return $user;
    }
}