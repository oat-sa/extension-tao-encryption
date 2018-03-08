<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 08/03/18
 * Time: 16:15
 */

namespace oat\taoEncryption\controller;

use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use oat\taoOauth\model\OauthController;

class EncryptionApi extends \tao_actions_RestController implements OauthController
{
    const PARAM_PUBLIC_KEY = 'public-key';

    public function getPublicKeyChecksum()
    {
        try {
            if ($this->getRequestMethod() != \Request::HTTP_GET) {
                throw new \BadMethodCallException('Only GET method is accepted to access ' . __FUNCTION__);
            }

            $this->returnJson([
                self::PARAM_PUBLIC_KEY => $this->getKeyPairService()->getPublicKeyChecksum()
            ]);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    public function savePublicKey()
    {
        try {
            if ($this->getRequestMethod() != \Request::HTTP_POST) {
                throw new \BadMethodCallException('Only POST method is accepted to access ' . __FUNCTION__);
            }

            \common_Logger::i(print_r($this->getRequest()->getRawParameters(), true));
//            $key = $this->getKeyPairService()->getKeyPairModel()->savePublicKey($key);

            $this->returnJson([
                'ok' => 'ook'
            ]);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * @return AsymmetricKeyPairProviderService
     */
    protected function getKeyPairService()
    {
        return $this->getServiceLocator()->get(AsymmetricKeyPairProviderService::SERVICE_ID);
    }
}