<?php


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