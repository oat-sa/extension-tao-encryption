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
use oat\taoEncryption\Service\Result\EncryptResultService;
use oat\taoResultServer\models\classes\implementation\ResultServerService;

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

        /** @var ResultServerService $resultServer */
        $resultServer = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultServer->setOption(ResultServerService::OPTION_RESULT_STORAGE, EncryptResultService::SERVICE_ID);

        $this->registerService(ResultServerService::SERVICE_ID, $resultServer);

        $report = Report::createSuccess('EncryptResultService setup with success');

        return $report;
    }
}