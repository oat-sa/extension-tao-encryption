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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\taoEncryption\scripts\tools;

use oat\generis\model\GenerisRdf;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\event\UserUpdatedEvent;
use oat\taoEncryption\Event\TestTakerUpdatedHandler;
use oat\taoEncryption\Event\UserUpdatedHandler;
use oat\taoTestTaker\models\events\TestTakerImportedEvent;
use oat\taoTestTaker\models\events\TestTakerUpdatedEvent;
use common_report_Report as Report;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupUserEventSubscription'
 */
class SetupUserEventSubscription extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);

        $eventManager->attach(TestTakerUpdatedEvent::class, [
            TestTakerUpdatedHandler::class,
            'handle'
        ]);

        $eventManager->attach(UserUpdatedEvent::class, [
            UserUpdatedHandler::class,
            'handle'
        ]);

        $eventManager->attach(TestTakerImportedEvent::class, [
            TestTakerUpdatedHandler::class,
            'handle'
        ]);

        $this->registerService(EventManager::SERVICE_ID, $eventManager);

        /** @var \common_ext_ExtensionsManager $extManager */
        $extManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        $taoTestTaker = $extManager->getExtensionById('taoTestTaker');
        $config = $taoTestTaker->getConfig('csvImporterCallbacks');
        $callbacks = $config['callbacks'];

        $passwordCallbacks = $callbacks[GenerisRdf::PROPERTY_USER_PASSWORD];

        if (false === array_search('oat\\taoTestTaker\\models\\TestTakerSavePasswordInMemory::saveUserPassword', $passwordCallbacks)){
            $callbacks[GenerisRdf::PROPERTY_USER_PASSWORD] = array_merge([
                'oat\\taoTestTaker\\models\\TestTakerSavePasswordInMemory::saveUserPassword'
            ], $callbacks[GenerisRdf::PROPERTY_USER_PASSWORD]);

            $taoTestTaker->setConfig('csvImporterCallbacks', array_merge($config, ['callbacks' => $callbacks]));
        }

        return Report::createSuccess('Events TestTakerUpdatedEvent UserUpdatedEvent subscription attached.');
    }
}