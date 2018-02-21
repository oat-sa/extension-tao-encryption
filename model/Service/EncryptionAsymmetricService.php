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

use oat\taoEncryption\Service\Algorithm\AlgorithmAsymmetricRSAServiceInterface;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;

class EncryptionAsymmetricService extends EncryptionServiceAbstract
{
    const SERVICE_ID = 'taoEncryption/asymmetricEncryptionService';

    const OPTION_ENCRYPTION_ALGORITHM = 'encryptionAlgorithm';

    const OPTION_KEY_PAIR_PROVIDER = 'keyPairProvider';

    /** @var AlgorithmAsymmetricRSAServiceInterface */
    private $service;
    /**
     * @return AlgorithmAsymmetricRSAServiceInterface
     * @throws \Exception
     */
    protected function getAlgorithm()
    {
        if (is_null($this->service)){
            $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_ALGORITHM));
            if (!$service instanceof AlgorithmAsymmetricRSAServiceInterface) {
                throw new \Exception('Incorrect algorithm service');
            }

            $keyPairProvider = $this->getServiceLocator()->get($this->getOption(static::OPTION_KEY_PAIR_PROVIDER));
            if (!$keyPairProvider instanceof AsymmetricKeyPairProviderService) {
                throw new \Exception('Incorrect service key pair provided');
            }
            $service->setKeyPairProvider($keyPairProvider);
            $this->service = $service;
        }

        return $this->service;
    }
}