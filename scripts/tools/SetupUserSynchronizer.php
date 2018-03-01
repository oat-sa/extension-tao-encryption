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
use oat\tao\model\TaoOntology;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Sync\EncryptAdministratorSynchronizer;
use oat\taoEncryption\Service\Sync\EncryptProctorSynchronizer;
use oat\taoEncryption\Service\Sync\EncryptTestTakerSynchronizer;
use oat\taoSync\model\Entity;
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
        $testTakerService = new EncryptTestTakerSynchronizer([
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => SimpleKeyProviderService::SERVICE_ID,
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTED_PROPERTIES => [
                OntologyRdfs::RDFS_LABEL,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_FIRSTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_LASTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_MAIL
            ],
            EncryptTestTakerSynchronizer::OPTIONS_EXCLUDED_FIELDS => [
                TaoOntology::PROPERTY_UPDATED_AT,
                Entity::CREATED_AT,
            ]
        ]);

        $this->registerService(EncryptTestTakerSynchronizer::SERVICE_ID, $testTakerService);


        $administratorService = new EncryptAdministratorSynchronizer([
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => SimpleKeyProviderService::SERVICE_ID,
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTED_PROPERTIES => [
                OntologyRdfs::RDFS_LABEL,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_FIRSTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_LASTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_MAIL
            ],
            EncryptTestTakerSynchronizer::OPTIONS_EXCLUDED_FIELDS => [
                TaoOntology::PROPERTY_UPDATED_AT,
                Entity::CREATED_AT,
            ]
        ]);

        $this->registerService(EncryptAdministratorSynchronizer::SERVICE_ID, $administratorService);

        $proctorService = new EncryptProctorSynchronizer([
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTION_SERVICE => EncryptionSymmetricService::SERVICE_ID,
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE => SimpleKeyProviderService::SERVICE_ID,
            EncryptTestTakerSynchronizer::OPTION_ENCRYPTED_PROPERTIES => [
                OntologyRdfs::RDFS_LABEL,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_FIRSTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_LASTNAME,
                \oat\generis\model\GenerisRdf::PROPERTY_USER_MAIL
            ],
            EncryptTestTakerSynchronizer::OPTIONS_EXCLUDED_FIELDS => [
                TaoOntology::PROPERTY_UPDATED_AT,
                Entity::CREATED_AT,
            ]
        ]);

        $this->registerService(EncryptProctorSynchronizer::SERVICE_ID, $proctorService);

        /** @var SyncService $syncService */
        $syncService = $this->getServiceLocator()->get(SyncService::SERVICE_ID);
        $synchronizers = $syncService->getOption(SyncService::OPTION_SYNCHRONIZERS);

        $syncService->setOptions(array_merge(
           $syncService->getOptions(),
           [
               SyncService::OPTION_SYNCHRONIZERS => array_merge($synchronizers,[
                   'administrator' => EncryptAdministratorSynchronizer::SERVICE_ID,
                   'proctor' => EncryptProctorSynchronizer::SERVICE_ID,
                   'test-taker' => EncryptTestTakerSynchronizer::SERVICE_ID,
               ])
           ]
        ));

        $this->registerService(SyncService::SERVICE_ID, $syncService);

        return Report::createSuccess('Synchronizers: administrator, proctor, test-taker overwrite');

    }
}