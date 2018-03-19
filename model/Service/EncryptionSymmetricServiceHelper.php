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

namespace oat\taoEncryption\Service;

use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use Zend\ServiceManager\ServiceLocatorInterface;

trait EncryptionSymmetricServiceHelper
{
    /** @var SimpleKeyProviderService */
    private $encryptionService;

    /** @return ServiceLocatorInterface */
    public abstract function getServiceLocator();

    /** @return string */
    protected abstract function getOptionEncryptionService();

    /** @return string */
    protected abstract function getOptionEncryptionKeyProvider();

    /**
     * @param string $key
     * @return EncryptionSymmetricService
     * @throws \Exception
     */
    public function getEncryptionService($key)
    {
        if (is_null($this->encryptionService)) {
            /** @var EncryptionSymmetricService $service */
            $service = $this->getServiceLocator()->get($this->getOptionEncryptionService());
            if (!$service instanceof EncryptionSymmetricService) {
                throw new  \Exception('Incorrect algorithm service provided');
            }

            $this->encryptionService = $service;
        }

        /** @var SimpleKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get($this->getOptionEncryptionKeyProvider());
        $keyProvider->setKey($key);
        $this->encryptionService->setKeyProvider($keyProvider);

        return $this->encryptionService;
    }
}