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

namespace oat\taoEncryption\Service\Algorithm;

use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\Service\KeyProvider\SymmetricProvider;
use oat\taoEncryption\Service\KeyProvider\SymmetricKeyProviderService;
use oat\taoEncryption\Model\Symmetric\Symmetric;
use oat\taoEncryption\Model\AlgorithmFactory;

class AlgorithmSymmetricService extends ConfigurableService implements AlgorithmServiceInterface, SymmetricProvider
{
    const SERVICE_ID = 'taoEncryption/symmetricAlgorithm';

    /**
     * Available: RC4, DES, 3DES, Rijndael, AES, Blowfish, Twofish
     */
    const OPTION_ALGORITHM = 'algorithm';

    /** @var SymmetricKeyProviderService */
    private $keyProvider;

    /** @var Symmetric  */
    private $algorithm;

    /**
     * @param SymmetricKeyProviderService $keyProviderService
     */
    public function setKeyProvider(SymmetricKeyProviderService $keyProviderService)
    {
        $this->keyProvider = $keyProviderService;
    }

    /**
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function encrypt($data)
    {
        return $this->getAlgorithm()->encrypt($this->getKeyProviderService()->getKey(), $data);
    }

    /**
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function decrypt($data)
    {
        return $this->getAlgorithm()->decrypt($this->getKeyProviderService()->getKey(), $data);
    }

    /**
     * @return array|SymmetricKeyProviderService|object
     * @throws \Exception
     */
    protected function getKeyProviderService()
    {
        if (is_null($this->keyProvider)) {
            throw new \Exception('Incorrect Key provider service, call setKeyProvider before.');
        }

        return $this->keyProvider;
    }

    /**
     * @return Symmetric
     * @throws \Exception
     */
    protected function getAlgorithm()
    {
        if (is_null($this->algorithm)) {
            $algorithmString = $this->getOption(static::OPTION_ALGORITHM);

            $this->algorithm = new Symmetric(AlgorithmFactory::create($algorithmString));
        }

        return $this->algorithm;
    }

}