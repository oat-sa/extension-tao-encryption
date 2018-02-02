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

namespace oat\Encryption\Model;

use oat\Encryption\Model\Exception\AlgorithmNotAvailableException;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\Blowfish;
use phpseclib\Crypt\DES;
use phpseclib\Crypt\RC4;
use phpseclib\Crypt\Rijndael;
use phpseclib\Crypt\TripleDES;
use phpseclib\Crypt\Twofish;

class AlgorithmFactory
{
    /**
     * @param $algorithm
     * @return Base
     * @throws \Exception
     */
    public static function create($algorithm)
    {
        switch ($algorithm) {
            case 'RC4':
                return new RC4();
            case 'DES':
                return new DES();
            case '3DES':
                return new TripleDES();
            case 'Rijndael':
                return new Rijndael();
            case 'AES':
                return new AES();
            case 'Blowfish':
                return new Blowfish();
            case 'Twofish':
                return new Twofish();
            default:
                throw new AlgorithmNotAvailableException('Algorithm encryption not available');
        }
    }
}