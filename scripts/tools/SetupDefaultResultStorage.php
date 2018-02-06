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

use oat\oatbox\extension\InstallAction;
use common_report_Report as Report;
use oat\taoEncryption\ExtendedService\EncryptedKeyValueResultStorage;
use oat\taoEncryption\ExtendedService\EncryptedRdsResultStorage;
use oat\taoOutcomeRds\model\RdsResultStorage;
use taoAltResultStorage_models_classes_KeyValueResultStorage;

/**
 * Class SwitchToDecryptedResultStorage
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupDefaultResultStorage'
 */
class SetupDefaultResultStorage extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        $report = Report::createSuccess();

        if ($this->getServiceLocator()->has(EncryptedKeyValueResultStorage::SERVICE_ID)) {
            $service = $this->getServiceLocator()->get(EncryptedKeyValueResultStorage::SERVICE_ID);
            if ($service instanceof EncryptedKeyValueResultStorage){
                $options = $service->getOptions();
                unset($options[EncryptedKeyValueResultStorage::OPTION_ENCRYPTION_SERVICE]);

                $keyValueResult = new taoAltResultStorage_models_classes_KeyValueResultStorage($options);

                $this->registerService(taoAltResultStorage_models_classes_KeyValueResultStorage::SERVICE_ID, $keyValueResult);

                $report->add(Report::createSuccess('taoAltResultStorage_models_classes_KeyValueResultStorage set'));
            }
        } else {
            $report->add(Report::createInfo('EncryptedKeyValueResultStorage not registered'));
        }

        if ($this->getServiceLocator()->has(EncryptedRdsResultStorage::SERVICE_ID)) {
            $service = $this->getServiceLocator()->get(EncryptedRdsResultStorage::SERVICE_ID);

            if ($service instanceof EncryptedRdsResultStorage) {
                $options = $service->getOptions();
                unset($options[EncryptedRdsResultStorage::OPTION_ENCRYPTION_SERVICE]);

                $rdsResult = new RdsResultStorage($options);

                $this->registerService(RdsResultStorage::SERVICE_ID, $rdsResult);

                $report->add(Report::createSuccess('RdsResultStorage set'));
            }
        } else {
            $report->add(Report::createInfo('EncryptedRdsResultStorage not registered'));

        }

        return $report;
    }
}