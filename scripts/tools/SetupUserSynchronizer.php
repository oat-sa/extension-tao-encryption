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

use oat\generis\model\OntologyRdfs;
use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Sync\EncryptUserSyncFormatter;
use oat\taoSync\model\synchronizer\user\administrator\RdfAdministratorSynchronizer;
use oat\taoSync\model\synchronizer\user\proctor\RdfProctorSynchronizer;
use oat\taoSync\model\synchronizer\user\testtaker\RdfTestTakerSynchronizer;
use oat\taoSync\model\synchronizer\user\UserSynchronizer;
use oat\taoSync\model\SyncService;
use common_report_Report as Report;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupUserSynchronizer'
 */
class SetupUserSynchronizer extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);

        if (!$extensionManager->isInstalled('taoSync')) {
            return Report::createSuccess('Cannot setup sync, to taoSync extension installed');
        }

        $encryptUserSyncFormatter = new EncryptUserSyncFormatter([
            EncryptUserSyncFormatter::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
            EncryptUserSyncFormatter::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => SimpleKeyProviderService::SERVICE_ID,
            EncryptUserSyncFormatter::OPTION_ENCRYPTED_PROPERTIES => [
                OntologyRdfs::RDFS_LABEL,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_FIRSTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_LASTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_MAIL
            ],
        ]);

        $this->registerService(EncryptUserSyncFormatter::SERVICE_ID, $encryptUserSyncFormatter);

        /** @var SyncService $syncService */
        $syncService = $this->getServiceLocator()->get(SyncService::SERVICE_ID);
        $synchronizers = $syncService->getOption(SyncService::OPTION_SYNCHRONIZERS);
        $syncOptions = $syncService->getOptions();

        $syncService->setOptions(array_merge(
            $syncOptions,
           [
               SyncService::OPTION_SYNCHRONIZERS => array_merge($synchronizers,[
                   'administrator' => new RdfAdministratorSynchronizer(array_merge(
                       $syncOptions[SyncService::OPTION_SYNCHRONIZERS]['administrator']->getOptions(),
                       [UserSynchronizer::OPTIONS_FORMATTER_CLASS => EncryptUserSyncFormatter::SERVICE_ID]
                   )),
                   'proctor' => new RdfProctorSynchronizer(array_merge(
                       $syncOptions[SyncService::OPTION_SYNCHRONIZERS]['proctor']->getOptions(),
                       [UserSynchronizer::OPTIONS_FORMATTER_CLASS => EncryptUserSyncFormatter::SERVICE_ID]
                   )),
                   'test-taker' => new RdfTestTakerSynchronizer(array_merge(
                       $syncOptions[SyncService::OPTION_SYNCHRONIZERS]['test-taker']->getOptions(),
                       [UserSynchronizer::OPTIONS_FORMATTER_CLASS => EncryptUserSyncFormatter::SERVICE_ID]
                   )),
               ])
           ]
        ));

        $this->registerService(SyncService::SERVICE_ID, $syncService);

        return Report::createSuccess('Synchronizers: administrator, proctor, test-taker overwrite');

    }
}