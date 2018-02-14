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
namespace oat\taoEncryption\Service\SessionState;

use oat\taoEncryption\Service\EncryptionServiceInterface;
use tao_models_classes_service_StateStorage;

class EncryptedStateStorage extends tao_models_classes_service_StateStorage
{
    const OPTION_ENCRYPTION_SERVICE = 'symmetricEncryptionService';

    /**
     * @return EncryptionServiceInterface
     * @throws \Exception
     */
    public function getEncryptionService()
    {
        $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_SERVICE));
        if (!$service instanceof EncryptionServiceInterface) {
            throw new  \Exception('Incorrect algorithm service provided');
        }

        return $service;
    }

    /**
     * @param string $userId
     * @param string $callId
     * @param string $data
     * @return bool
     * @throws \Exception
     */
    public function set($userId, $callId, $data)
    {
        return parent::set($userId, $callId, $this->getEncryptionService()->encrypt($data));
    }

    /**
     * @param string $userId
     * @param string $callId
     * @return string
     * @throws \Exception
     */
    public function get($userId, $callId)
    {
        $value = parent::get($userId, $callId);
        if (is_null($value)) {
            return null;
        }

        return $this->getEncryptionService()->decrypt($value);
    }
}