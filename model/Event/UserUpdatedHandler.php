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

namespace oat\taoEncryption\Event;


use oat\generis\model\GenerisRdf;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\event\UserUpdatedEvent;
use oat\taoEncryption\Rdf\EncryptedUserRdf;
use oat\taoEncryption\Service\User\UserHandlerKeys;

class UserUpdatedHandler
{
    /**
     * @param UserUpdatedEvent $event
     * @throws \Exception
     */
    public static function handle(UserUpdatedEvent $event)
    {
        $eventData = $event->jsonSerialize();
        $userResource = new \core_kernel_classes_Resource($eventData['uri']);
        if (!isset($eventData['data'][GenerisRdf::PROPERTY_USER_PASSWORD])){
            return;
        }
        $userAddKeys = new UserHandlerKeys();
        ServiceManager::getServiceManager()->propagate($userAddKeys);
        $hashForKey = $eventData['data']['hashForKey'];
        $salt = $eventData['data'][GenerisRdf::PROPERTY_USER_PASSWORD];

        $userResource->editPropertyValues(
            new \core_kernel_classes_Property(EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY),
            $userAddKeys->generateUserKey($hashForKey, $salt)
        );

        $userResource->editPropertyValues(
            new \core_kernel_classes_Property(EncryptedUserRdf::PROPERTY_ENCRYPTION_PUBLIC_KEY),
            $userAddKeys->encryptApplicationKey($hashForKey)
        );
    }
}