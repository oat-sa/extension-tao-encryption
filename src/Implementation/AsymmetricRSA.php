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
namespace oat\Encryption\Implementation;

use oat\Encryption\Interfaces\Encrypt;
use oat\Encryption\Model\Key;
use oat\Encryption\Model\PrivateKey;
use oat\Encryption\Model\PublicKey;
use phpseclib\Crypt\RSA;

class AsymmetricRSA implements Encrypt
{
    /** @var  */
    private $crypter;

    public function __construct()
    {
        $this->crypter = new RSA();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function encrypt(Key $key, $data)
    {
        if (!$key instanceof PublicKey) {
            throw new \Exception('Key needs to be a public key');
        }
        $this->crypter->loadKey($key->getKey());

        return $this->crypter->encrypt($data);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function decrypt(Key $key, $data)
    {
        if (!$key instanceof PrivateKey) {
            throw new \Exception('Key needs to be a private key');
        }
        $this->crypter->loadKey($key->getKey());

        return $this->crypter->decrypt($data);
    }
}