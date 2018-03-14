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

namespace oat\taoEncryption\Service\User;

use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Session\GenerateKey;

class UserHandlerKeys extends ConfigurableService
{
    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    public function generateUserKey($password, $salt)
    {
        return GenerateKey::generate($password, $salt);
    }

    /**
     * @param $plainPassword
     * @return string
     * @throws \Exception
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function encryptApplicationKey($plainPassword)
    {
        /** @var EncryptionSymmetricService $encryptService */
        $encryptService = $this->getServiceLocator()->get(EncryptionSymmetricService::SERVICE_ID);
        /** @var SimpleKeyProviderService $simpleKeyProvider */
        $simpleKeyProvider = $this->getServiceLocator()->get(SimpleKeyProviderService::SERVICE_ID);

        $simpleKeyProvider->setKey($plainPassword);
        $encryptService->setKeyProvider($simpleKeyProvider);

        /** @var FileKeyProviderService $fileKeyProvider */
        $fileKeyProvider = $this->getServiceLocator()->get(FileKeyProviderService::SERVICE_ID);

        return base64_encode($encryptService->encrypt($fileKeyProvider->getKeyFromFileSystem()));
    }
}