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

use oat\taoEncryption\Service\Algorithm\AlgorithmServiceInterface;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\KeyProvider\DummyKeyProvider;
use oat\taoEncryption\Service\KeyProvider\SymmetricKeyProviderService;

class EncryptionSymmetricService extends EncryptionServiceAbstract
{
    const SERVICE_ID = 'taoEncryption/symmetricEncryptionService';

    const OPTION_ENCRYPTION_ALGORITHM = 'encryptionAlgorithm';

    /** @var AlgorithmSymmetricService */
    private $algorithm;

    /** @var SymmetricKeyProviderService */
    private $keyProvider;

    /**
     * @return AlgorithmServiceInterface
     * @throws \Exception
     */
    public function getAlgorithm()
    {
        if (is_null($this->algorithm)) {
            $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_ALGORITHM));

            if (!$service instanceof AlgorithmSymmetricService) {
                throw new  \Exception('Incorrect algorithm service provided');
            }

            if (is_null($this->keyProvider)) {
                $keyProvider = new DummyKeyProvider();
            } else {
                $keyProvider = $this->keyProvider;
            }

            $service->setKeyProvider($keyProvider);

            $this->algorithm = $service;
        }

        return $this->algorithm;
    }

    /**
     * @param SymmetricKeyProviderService $keyProviderService
     */
    public function setKeyProvider(SymmetricKeyProviderService $keyProviderService)
    {
        $this->keyProvider = $keyProviderService;
    }
}