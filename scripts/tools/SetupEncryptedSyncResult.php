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

use common_ext_ExtensionsManager;
use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\Service\EncryptionAsymmetricService;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use oat\taoEncryption\Service\Mapper\DummyMapper;
use oat\taoEncryption\Service\Result\SyncEncryptedResultDataFormatter;
use oat\taoEncryption\Service\Result\SyncEncryptedResultService;
use oat\taoSync\model\event\SynchronisationStart;
use oat\taoSync\model\Result\SyncResultDataFormatter;
use oat\taoSync\model\ResultService;
use common_report_Report as Report;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedSyncResult'
 */
class SetupEncryptedSyncResult extends InstallAction
{
    /**
     * Register taoEncryption services for synchronisation if taoSync is installed.
     *
     * @param $params
     * @return Report
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    public function __invoke($params)
    {
        /** @var common_ext_ExtensionsManager $extManger */
        $extManger = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);

        if (!$extManger->isInstalled('taoSync')) {
            return Report::createFailure('taoSync extension not installed.');
        }

        $report = Report::createInfo('Configuring SyncEncryptedResult service...');

        $mapper = new DummyMapper();
        $this->getServiceManager()->register(DummyMapper::SERVICE_ID, $mapper);

        /** @var ResultService $stateStorage */
        $stateStorage = $this->getServiceLocator()->get(ResultService::SERVICE_ID);
        $options = $stateStorage->getOptions();

        $encrypted = new SyncEncryptedResultService(array_merge([
                SyncEncryptedResultService::OPTION_PERSISTENCE => 'encryptedResults',
                SyncEncryptedResultService::OPTION_USER_ID_CLIENT_TO_USER_ID_CENTRAL => DummyMapper::SERVICE_ID,
                SyncEncryptedResultService::OPTION_ENCRYPTION_SERVICE => EncryptionAsymmetricService::SERVICE_ID,
            ], $options)
        );

        $this->registerService(SyncEncryptedResultService::SERVICE_ID, $encrypted);

        $report->add(Report::createSuccess('SyncEncryptedResultService successfully registered.'));

        /** @var SyncResultDataFormatter $formatter */
        $dataFormatter = $this->getServiceLocator()->get(SyncResultDataFormatter::SERVICE_ID);
        $options = $dataFormatter->getOptions();

        $encryptedDataFormatter = new SyncEncryptedResultDataFormatter(array_merge([
            SyncEncryptedResultDataFormatter::OPTION_PERSISTENCE => 'encryptedResults'
        ], $options));
        $this->registerService(SyncEncryptedResultDataFormatter::SERVICE_ID, $encryptedDataFormatter);

        $report->add(Report::createSuccess('SyncEncryptedResultDataFormatter successfully registered.'));
        $report->add(Report::createSuccess($this->setupSynchronizationListener()));

        return $report;
    }

    /**
     * If taoSync is installed, prepare synchronization to be aware of taoEncryption
     *
     * During synchronization process, public key will be synchronized againt remote host
     *
     * @return Report
     */
    protected function setupSynchronizationListener()
    {
        $this->registerEvent(
            SynchronisationStart::class,
            [AsymmetricKeyPairProviderService::class, 'onSynchronisationStarted']
        );

        return Report::createSuccess('Synchronisation event successfully configured to synchronize encryption key.');
    }
}