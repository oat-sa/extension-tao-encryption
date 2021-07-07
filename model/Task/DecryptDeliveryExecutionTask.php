<?php

namespace oat\taoEncryption\Task;

use oat\oatbox\extension\AbstractAction;
use oat\taoEncryption\Service\Result\DecryptResultService;

class DecryptDeliveryExecutionTask extends AbstractAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_exception_Error
     * @throws \Exception
     */
    public function __invoke($params)
    {
        if (!isset($params['deliveryExecutionId'])) {
            throw new \Exception('The deliveryExecutionId it is not in the params');
        }

        if (!isset($params['deliveryId'])) {
            throw new \Exception('The deliveryId it is not in the params');
        }

        $deliveryExecutionId = $params['deliveryExecutionId'];
        $deliveryId = $params['deliveryId'];
        /** @var DecryptResultService $decryptService */
        $decryptService = $this->getServiceLocator()->get(DecryptResultService::SERVICE_ID);

        return $decryptService->decryptByExecution($deliveryId, $deliveryExecutionId);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }
}
