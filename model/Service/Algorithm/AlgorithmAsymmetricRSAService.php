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
use oat\taoEncryption\Service\KeyProvider\AsymmetricProvider;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use oat\taoEncryption\Model\Asymmetric\AsymmetricRSA;
use oat\taoEncryption\Model\AlgorithmFactory;
use phpseclib\Crypt\AES;

class AlgorithmAsymmetricRSAService extends ConfigurableService implements AlgorithmServiceInterface, AsymmetricProvider
{
    const SERVICE_ID = 'taoEncryption/asymmetricAlgorithm';

    /**
     * Available: RC4, DES, 3DES, Rijndael, AES, Blowfish, Twofish
     */
    const OPTION_ALGORITHM = 'algorithm';

    /** @var AsymmetricKeyPairProviderService */
    private $keyPairService;

    /** @var AsymmetricRSA  */
    private $algorithmRsa;

    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->algorithmRsa = new AsymmetricRSA(AlgorithmFactory::create($this->getOption(static::OPTION_ALGORITHM)));
    }

    /**
     * @param AsymmetricKeyPairProviderService $keyPairProviderService
     * @return mixed|void
     */
    public function setKeyPairProvider(AsymmetricKeyPairProviderService $keyPairProviderService)
    {
        $this->keyPairService = $keyPairProviderService;
    }

    /**
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function encrypt($data)
    {
        return $this->algorithmRsa->encrypt($this->getKeyPairService()->getPublicKey(), $data);
    }

    /**
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function decrypt($data)
    {
        return $this->algorithmRsa->decrypt($this->getKeyPairService()->getPrivateKey(), $data);
    }

    /**
     * @return array|AsymmetricKeyPairProviderService|object
     * @throws \Exception
     */
    protected function getKeyPairService()
    {
        if (is_null($this->keyPairService)) {
            throw new \Exception('Incorrect Key Pair provider service, call setKeyPairProvider before');
        }

        return $this->keyPairService;
    }
}