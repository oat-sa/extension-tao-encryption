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
use oat\oatbox\action\Action;
use oat\taoEncryption\Service\Result\DecryptResultService;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\DecryptResults' <delivery_id>
 */
class DecryptResults implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var Report */
    private $report;
    /**
     * @param $params
     * @return Report
     * @throws \common_exception_Error
     */
    public function __invoke($params)
    {
        $time_start = microtime(true);

        $deliveryExecId = isset($params[0]) ? $params[0] : null;
        if ($deliveryExecId === null){
            return Report::createFailure('incorrect result id provided: '. $deliveryExecId);
        }

        /** @var DecryptResultService $service */
        $service = $this->getServiceLocator()->get(DecryptResultService::SERVICE_ID);

        $this->report = Report::createSuccess('Decrypting Results');
        try {

            $service->decrypt($deliveryExecId);

        }catch (DecryptionFailedException $exception){
            $this->report = Report::createFailure($exception->getMessage());
        }

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start)/60;

        $this->report->add(new Report(Report::TYPE_INFO, 'Time:' . round($execution_time, 4) .' Minutes.' ));

        return $this->report;
    }
}