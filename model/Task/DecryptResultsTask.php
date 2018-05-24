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
 */

namespace oat\taoEncryption\Task;

use oat\oatbox\task\AbstractTaskAction;
use common_report_Report as Report;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoEncryption\Service\Result\DecryptResultService;

class DecryptResultsTask extends AbstractTaskAction implements \JsonSerializable
{
    const OPTION_DELIVERY_IDS = 'deliveryIds';

    public function jsonSerialize()
    {
        return __CLASS__;
    }

    /**
     * @param $params
     * @return Report
     * @throws \common_exception_Error
     */
    public function __invoke($params)
    {
        if (!isset($params[static::OPTION_DELIVERY_IDS])){
            return Report::createFailure("No Deliveries Ids.");
        }
        $deliveriesIds = $params[static::OPTION_DELIVERY_IDS];

        $report = Report::createInfo('Decryption Start');

        foreach ($deliveriesIds as $deliveryId){
            try{
                $report->add($this->decryptResultsForDelivery($deliveryId));
            }catch (\Exception $exception){
                $report->add(Report::createFailure($exception->getMessage()));
            }
        }

        return $report;
    }

    /**
     * @param $deliveryId
     * @return Report
     * @throws \common_exception_Error
     */
    protected function decryptResultsForDelivery($deliveryId)
    {
        /** @var DecryptResultService $service */
        $service = $this->getServiceLocator()->get(DecryptResultService::SERVICE_ID);
        try {

            $report = $service->decrypt($deliveryId);
        }catch (DecryptionFailedException $exception){
            $report = Report::createFailure($exception->getMessage());
        }

        return $report;
    }
}