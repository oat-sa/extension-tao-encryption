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

use oat\taoEncryption\Model\Symmetric\Symmetric;
use oat\taoEncryption\Service\KeyProvider\SymmetricKeyProviderService;
use oat\taoEncryption\Service\KeyProvider\SymmetricProvider;

interface AlgorithmSymmetricServiceInterface extends AlgorithmServiceInterface, SymmetricProvider
{
    /**
     * @param SymmetricKeyProviderService $keyProviderService
     */
    public function setKeyProvider(SymmetricKeyProviderService $keyProviderService);

    /**
     * @param Symmetric $algorithm
     * @return mixed
     */
    public function setAlgorithm(Symmetric $algorithm);
    /**
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function encrypt($data);

    /**
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function decrypt($data);
}