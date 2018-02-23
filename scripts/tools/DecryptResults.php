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
use oat\oatbox\extension\script\ScriptAction;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoEncryption\Service\Result\DecryptResultService;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\DecryptResults' <delivery_id>
 */
class DecryptResults extends ScriptAction
{
    /** @var Report */
    private $report;

    /**
     * @return bool
     */
    protected function showTime()
    {
        return true;
    }

    protected function provideOptions()
    {
        return [
            'delivery_id' => [
                'prefix' => 'd',
                'longPrefix' => 'delivery_id',
                'required' => false,
                'description' => 'A delivery id identifier'
            ],
            'all' => [
                'flag' => true,
                'prefix' => 'all',
                'longPrefix' => 'all',
                'required' => false,
                'description' => 'All deliveries results will be decrypted'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Decrypt Results of a delivery.';
    }

    /**
     * Run Script.
     *
     * Run the userland script. Implementers will use this method
     * to implement the main logic of the script.
     *
     * @return \common_report_Report
     * @throws \common_exception_Error
     */
    protected function run()
    {
        $this->report = Report::createSuccess('Decrypting Results');

        if ($this->hasOption('all')){
            /** @var \core_kernel_classes_Resource $delivery */
            foreach ($this->getDeliveryAssemblyService()->getAllAssemblies() as $delivery) {
                $deliveryId = $delivery->getUri();
                $this->decryptResultsForDelivery($deliveryId);
            }
        } elseif ($this->hasOption('delivery_id')) {
            $deliveryId = $this->getOption('delivery_id');
            if ($deliveryId === null){
                return Report::createFailure('incorrect result id provided: '. $deliveryId);
            }
            $this->decryptResultsForDelivery($deliveryId);
        }

        return $this->report;
    }

    /**
     * @param $deliveryId
     * @throws \common_exception_Error
     */
    protected function decryptResultsForDelivery($deliveryId)
    {
        /** @var DecryptResultService $service */
        $service = $this->getServiceLocator()->get(DecryptResultService::SERVICE_ID);
        try {

            $service->decrypt($deliveryId);

        }catch (DecryptionFailedException $exception){
            $this->report = Report::createFailure($exception->getMessage());
        }

        $this->report->add(Report::createSuccess('Delivery: '. $deliveryId . ' results successfully decrypted'));
    }

    /**
     * @return DeliveryAssemblyService
     */
    protected function getDeliveryAssemblyService()
    {
        return DeliveryAssemblyService::singleton();
    }
}