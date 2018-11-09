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
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 */

namespace oat\taoEncryption\Event;

use oat\taoEncryption\Rdf\EncryptedDeliveryRdf;
use oat\taoTestCenter\model\event\ProctorCreatedEvent;
use \core_kernel_classes_Property as Property;

class ProctorCreatedHandler
{

    /**
     * @param ProctorCreatedEvent $event
     * @throws \Exception
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public static function handle(ProctorCreatedEvent $event)
    {
        $tcAdmin = $event->getTestCenterAdmin();
        $proctor = $event->getProctor();

        $appKeyProperty = new Property(EncryptedDeliveryRdf::PROPERTY_APPLICATION_KEY);

        $appKey = $tcAdmin->getOnePropertyValue($appKeyProperty);
        if (!empty($appKey)) {
            $proctor->setPropertyValue($appKeyProperty, $appKey);
        }
    }
}