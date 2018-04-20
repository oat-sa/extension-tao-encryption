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

namespace oat\taoEncryption\controller;

use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\log\TaoLoggerAwareInterface;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use oat\taoOauth\model\OauthController;

class EncryptionApi extends \tao_actions_RestController implements TaoLoggerAwareInterface
{
    use LoggerAwareTrait;

    const PARAM_REQUIRE_UPDATE = 'require-update';
    const PARAM_PUBLIC_KEY = 'public-key';
    const PARAM_CHECKSUM = 'public-checksum';

    /**
     * API to compare incoming public key checksum with local
     *
     * If checksum check is ok, returns `'require-update' => false`
     * If it is not ok, returns `'require-update' => true` with new checksum to update
     *
     * @throws \common_exception_NotImplemented
     */
    public function updatePublicKey()
    {
        try {
            if ($this->getRequestMethod() != \Request::HTTP_POST) {
                throw new \BadMethodCallException('Only POST method is accepted to access ' . __FUNCTION__);
            }

            $parameters = file_get_contents('php://input');
            if (
                is_array($parameters = json_decode($parameters, true))
                && json_last_error() === JSON_ERROR_NONE
                && array_key_exists(self::PARAM_CHECKSUM, $parameters)
            ) {
                $checksum = isset($parameters[self::PARAM_CHECKSUM]) ? $parameters[self::PARAM_CHECKSUM] : null ;
            } else {
                throw new \InvalidArgumentException('A valid "' . self::PARAM_CHECKSUM . '" parameter is required to access ' . __FUNCTION__);
            }
        } catch (\LogicException $e) {
            $this->logError($e->getMessage());
            $this->returnFailure($e);
            return;
        }

        try {
            $publicKey = $this->getKeyPairService()->getPublicKey()->getKey();
            $requireUpdate = !$this->getKeyPairService()->comparePublicKeyChecksum($checksum, $publicKey);
        } catch (\Exception $e) {
            $requireUpdate = false;
            $publicKey = null;
        }

        $this->returnJson([
            self::PARAM_REQUIRE_UPDATE => $requireUpdate,
            self::PARAM_PUBLIC_KEY => $requireUpdate ? $publicKey : null
        ]);
    }

    /**
     * @return AsymmetricKeyPairProviderService
     */
    protected function getKeyPairService()
    {
        return $this->getServiceLocator()->get(AsymmetricKeyPairProviderService::SERVICE_ID);
    }
}