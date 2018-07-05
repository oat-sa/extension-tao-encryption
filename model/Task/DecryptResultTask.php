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

use oat\oatbox\extension\AbstractAction;
use oat\taoEncryption\Service\Result\DecryptResultService;

class DecryptResultTask extends AbstractAction implements \JsonSerializable
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_exception_Error
     * @throws \Exception
     */
    public function __invoke($params)
    {
        if (!isset($params['deliveryId'])){
            throw new \Exception('The delivery id it is not in the params');
        }

        $deliveryId = $params['deliveryId'];
        /** @var DecryptResultService $decryptService */
        $decryptService = $this->getServiceLocator()->get(DecryptResultService::SERVICE_ID);

        return $decryptService->decrypt($deliveryId);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }
}