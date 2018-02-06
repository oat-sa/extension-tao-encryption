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

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\ExtendedService\EncryptedKeyValueResultStorage;
use oat\taoEncryption\ExtendedService\EncryptedRdsResultStorage;
use oat\taoOutcomeRds\model\RdsResultStorage;
use taoAltResultStorage_models_classes_KeyValueResultStorage;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupEncryptedResultStorage'
 */
class SetupEncryptedResultStorage extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $report = Report::createSuccess();

        if ($this->getServiceLocator()->has(taoAltResultStorage_models_classes_KeyValueResultStorage::SERVICE_ID)) {

            $service = $this->getServiceLocator()->get(taoAltResultStorage_models_classes_KeyValueResultStorage::SERVICE_ID);
            $options = $service->getOptions();

            $encryptedKeyValueResult = new EncryptedKeyValueResultStorage(array_merge([
                EncryptedKeyValueResultStorage::OPTION_ENCRYPTION_SERVICE => 'taoEncryption/asymmetricEncryptionService'
            ], $options));

            $this->registerService(EncryptedKeyValueResultStorage::SERVICE_ID, $encryptedKeyValueResult);

            $report->add(Report::createSuccess('EncryptedKeyValueResultStorage set'));
        } else {
            $report->add(Report::createInfo('taoAltResultStorage_models_classes_KeyValueResultStorage not registered'));
        }

        if ($this->getServiceLocator()->has(RdsResultStorage::SERVICE_ID)) {
            $service = $this->getServiceLocator()->get(RdsResultStorage::SERVICE_ID);
            $options = $service->getOptions();

            $encryptedRdsResult = new EncryptedRdsResultStorage(array_merge([
                EncryptedRdsResultStorage::OPTION_ENCRYPTION_SERVICE => 'taoEncryption/asymmetricEncryptionService'
            ], $options));

            $this->registerService(EncryptedRdsResultStorage::SERVICE_ID, $encryptedRdsResult);

            $report->add(Report::createSuccess('EncryptedRdsResultStorage set'));
        } else {
            $report->add(Report::createInfo('RdsResultStorage not registered'));
        }

        return $report;
    }
}