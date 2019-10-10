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
 *
 */
namespace oat\taoEncryption\scripts\update;

use common_ext_ExtensionUpdater;
use core_kernel_users_GenerisUser;
use oat\oatbox\event\EventManager;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoEncryption\Event\ProctorCreatedHandler;
use oat\taoEncryption\scripts\tools\SetupDecryptDeliveryLogFormatterService;
use oat\taoEncryption\Service\EncryptionAsymmetricService;
use oat\taoEncryption\Service\Mapper\DummyMapper;
use oat\taoEncryption\Service\Result\DecryptResultService;
use oat\taoEncryption\Service\Result\StoreVariableService;
use oat\taoEncryption\Service\Result\SyncEncryptedResultDataFormatter;
use oat\taoEncryption\Service\Result\SyncEncryptedResultService;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\TestSession\EncryptSyncTestSessionService;
use oat\taoEncryption\Service\KeyProvider\KeyProviderClient;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\User\EncryptedUserFactoryService;
use oat\taoEncryption\scripts\install\RegisterTestSessionSyncMapper;
use oat\taoSync\model\Result\SyncResultDataFormatter;
use oat\taoSync\model\ResultService;
use oat\taoSync\model\TestSession\SyncTestSessionServiceInterface;
use oat\taoTestCenter\model\event\ProctorCreatedEvent;

class Updater extends common_ext_ExtensionUpdater
{
    /**
     * @param $initialVersion
     * @return string|void
     * @throws \Exception
     */
    public function update($initialVersion)
    {
        $this->skip('0.1.0', '0.4.0');

        if ($this->isVersion('0.4.0')) {
            OntologyUpdater::syncModels();
            $this->getServiceManager()->register(KeyProviderClient::SERVICE_ID, new KeyProviderClient());
            AclProxy::applyRule(
                new AccessRule(
                    AccessRule::GRANT,
                    'http://www.tao.lu/Ontologies/generis.rdf#EncryptionRole',
                    array('ext'=>'taoEncryption', 'mod' => 'EncryptionApi')
                )
            );
            $this->setVersion('0.5.0');
        }

        if ($this->isVersion('0.5.0')){
            $simpleKeyProvider = new SimpleKeyProviderService([]);

            $this->getServiceManager()->register(SimpleKeyProviderService::SERVICE_ID, $simpleKeyProvider);

            $fileKeyProvider = new FileKeyProviderService([
                FileKeyProviderService::OPTION_FILESYSTEM_ID => 'keysEncryption'
            ]);

            $this->getServiceManager()->register(FileKeyProviderService::SERVICE_ID, $fileKeyProvider);

            $this->setVersion('0.6.0');
        }

        if ($this->isVersion('0.6.0')){
            $userFactory = $this->getServiceManager()->get(EncryptedUserFactoryService::SERVICE_ID);
            $userFactory->setOption(EncryptedUserFactoryService::OPTION_USER_CLASS_WRAPPED, core_kernel_users_GenerisUser::class);

            $this->getServiceManager()->register(EncryptedUserFactoryService::SERVICE_ID, $userFactory);
            $this->setVersion('0.6.1');
        }

        $this->skip('0.6.1', '0.11.2');

        if ($this->isVersion('0.11.2')) {
            $script = new RegisterTestSessionSyncMapper();
            $this->getServiceManager()->propagate($script);
            $script->__invoke([]);

            $this->getServiceManager()->register(SyncTestSessionServiceInterface::SERVICE_ID, new EncryptSyncTestSessionService());

            $this->setVersion('0.12.0');
        }

        $this->skip('0.12.0', '0.13.0');

        if ($this->isVersion('0.13.0')) {
            $setup = new SetupDecryptDeliveryLogFormatterService();
            $this->getServiceManager()->propagate($setup);
            $setup->__invoke([]);

            $this->setVersion('0.13.1');
        }

        if ($this->isVersion('0.13.1')) {
            OntologyUpdater::syncModels();
            $this->setVersion('0.14.0');
        }

        $this->skip('0.14.0', '0.16.1');

        if ($this->isVersion('0.16.1')) {
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(ProctorCreatedEvent::class, [ProctorCreatedHandler::class, 'handle']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('1.0.0');
        }
        $this->skip('1.0.0', '1.1.0');
      
        if ($this->isVersion('1.1.0')) {
            $dummyMapper = new DummyMapper();
            $this->getServiceManager()->register(DummyMapper::SERVICE_ID, $dummyMapper);

            /** @var SyncEncryptedResultService $syncResults */
            $syncResults = $this->getServiceManager()->get(SyncEncryptedResultService::SERVICE_ID);
            $syncResults->setOption(SyncEncryptedResultService::OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL, $dummyMapper);

            $this->getServiceManager()->register(SyncEncryptedResultService::SERVICE_ID, $syncResults);

            $storeVariableStore = new StoreVariableService();
            $this->getServiceManager()->register(StoreVariableService::SERVICE_ID, $storeVariableStore);

            /** @var DecryptResultService $decryptResult */
            $decryptResult = $this->getServiceManager()->get(DecryptResultService::SERVICE_ID);
            $decryptResult->setOption(DecryptResultService::OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL, DummyMapper::SERVICE_ID);
            $decryptResult->setOption(DecryptResultService::OPTION_STORE_VARIABLE_SERVICE, StoreVariableService::SERVICE_ID);

            $this->getServiceManager()->register(DecryptResultService::SERVICE_ID, $decryptResult);

            $this->setVersion('1.2.0');
        }

        $this->skip('1.2.0', '3.0.0');

        if ($this->isVersion('3.0.0')) {
            $service = $this->getServiceManager()->get(ResultService::SERVICE_ID);
            // register 'SyncEncryptedResultDataFormatter' if 'SetupEncryptedSyncResult' script was already executed
            if (is_object($service) && $service instanceof SyncEncryptedResultService) {
                /** @var SyncResultDataFormatter $formatter */
                $dataFormatter = $this->getServiceManager()->get(SyncResultDataFormatter::SERVICE_ID);
                $options = $dataFormatter->getOptions();

                $encryptedDataFormatter = new SyncEncryptedResultDataFormatter(array_merge([
                    SyncEncryptedResultDataFormatter::OPTION_PERSISTENCE => 'encryptedResults'
                ], $options));
                $this->getServiceManager()->register(SyncEncryptedResultDataFormatter::SERVICE_ID, $encryptedDataFormatter);
            }

            $this->setVersion('3.1.0');
        }

        $this->skip('3.1.0', '3.2.0');

        if ($this->isVersion('3.2.0')) {
            // several config option values of SyncEncryptedResultService might have been lost during taoSync '6.6.0' update
            // this restores any missing default values
            $service = $this->getServiceManager()->get(ResultService::SERVICE_ID);
            if (is_object($service) && $service instanceof SyncEncryptedResultService) {
                $options = $service->getOptions();

                $encryptedService = new SyncEncryptedResultService(array_merge([
                        SyncEncryptedResultService::OPTION_PERSISTENCE => 'encryptedResults',
                        SyncEncryptedResultService::OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL => DummyMapper::SERVICE_ID,
                        SyncEncryptedResultService::OPTION_ENCRYPTION_SERVICE => EncryptionAsymmetricService::SERVICE_ID,
                    ], $options)
                );

                $this->getServiceManager()->register(SyncEncryptedResultService::SERVICE_ID, $encryptedService);
            }

            $this->setVersion('3.2.1');
        }

        $this->skip('3.2.1', '4.0.0');
    }
}
