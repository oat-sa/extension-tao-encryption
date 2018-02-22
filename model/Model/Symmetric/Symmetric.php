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
namespace oat\taoEncryption\Model\Symmetric;

use oat\taoEncryption\Model\Encrypt;
use oat\taoEncryption\Model\Key;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\RC4;

class Symmetric implements Encrypt
{
    /** @var RC4 */
    private $crypter;

    /**
     * Symmetric constructor.
     * @param Base $cripter
     */
    public function __construct(Base $cripter)
    {
        $this->crypter = $cripter;
    }

    /**
     * @param string $data
     * @return mixed|string
     */
    public function encrypt(Key $key, $data)
    {
        $this->crypter->setKey($key->getKey());

        return $this->crypter->encrypt($data);
    }

    /**
     * @param $data
     * @return string
     */
    public function decrypt(Key $key, $data)
    {
        $this->crypter->setKey($key->getKey());

        return $this->crypter->decrypt($data);
    }
}