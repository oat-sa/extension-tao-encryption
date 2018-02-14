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
namespace oat\taoEncryption\Model\Asymmetric;

use League\Flysystem\FilesystemInterface;
use oat\taoEncryption\Model\KeyPairEncryption;
use oat\taoEncryption\Model\PrivateKey;
use oat\taoEncryption\Model\PublicKey;
use phpseclib\Crypt\RSA;

class AsymmetricRSAKeyPairProvider implements AsymmetricKeyPairProvider
{
    /** @var $filesystem */
    private $fileSystem;
    /** @var RSA */
    private $rsaEncryption;

    /**
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->rsaEncryption = new RSA();
        $this->fileSystem = $filesystem;
    }

    /**
     * @return KeyPairEncryption
     */
    public function generate()
    {
        $keyPair = $this->rsaEncryption->createKey();
        $publicKey = new PublicKey($keyPair['publickey']);
        $privateKey = new PrivateKey($keyPair['privatekey']);

        return new KeyPairEncryption($privateKey, $publicKey);
    }

    /**
     * @inheritdoc
     */
    public function savePublicKey(PublicKey $key)
    {
        return $this->fileSystem->put('public.key', $key->getKey());
    }

    /**
     * @inheritdoc
     */
    public function savePrivateKey(PrivateKey $key)
    {
        return $this->fileSystem->put('private.key', $key->getKey());
    }

    /**
     * @inheritdoc
     */
    public function getPublicKey()
    {
        return new PublicKey($this->fileSystem->read('public.key'));
    }

    /**
     * @inheritdoc
     */
    public function getPrivateKey()
    {
        return new PrivateKey($this->fileSystem->read('private.key'));
    }

}