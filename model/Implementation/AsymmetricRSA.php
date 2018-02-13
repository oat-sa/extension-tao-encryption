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
namespace oat\taoEncryption\Implementation;

use oat\taoEncryption\Interfaces\Encrypt;
use oat\taoEncryption\Model\Key;
use oat\taoEncryption\Model\PrivateKey;
use oat\taoEncryption\Model\PublicKey;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\Random;
use phpseclib\Crypt\RSA;

class AsymmetricRSA implements Encrypt
{
    /** @var RSA */
    private $crypterKey;

    /** @var AES */
    private $crypter;

    public function __construct(Base $crypter)
    {
        $this->crypterKey = new RSA();
        $this->crypter = $crypter;
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
        // Generate Random Symmetric Key
        $symKey = Random::string(150);
        // Encrypt Message with new Symmetric Key
        $encryptedText = $this->encryptMessageWithSymmetricKey($symKey, $data);
        // Encrypted the Symmetric Key with the Asymmetric Key
        $encryptedSymKey = $this->encryptSymmetricKeyWithAsymmetricKey($symKey, $key);

        return $this->encryptFinal($encryptedSymKey, $encryptedText);
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
        // Extract the Symmetric Key
        $encryptedSymKey = $this->extractEncryptedSymmetricKey($data);
        //Extract the encrypted message
        $encryptedText = $this->extractEncryptedMessage($data);

        // Decrypt the encrypted symmetric key
        $this->crypterKey->loadKey($key->getKey());
        $symKey = $this->crypterKey->decrypt($encryptedSymKey);

        $this->crypter->setKey($symKey);
        $decrypted = $this->crypter->decrypt($encryptedText);

        return $decrypted;
    }

    /**
     * @param $symKey
     * @param $data
     * @return string
     */
    private function encryptMessageWithSymmetricKey($symKey, $data)
    {
        $this->crypter->setKey($symKey);
        $encryptedText = $this->crypter->encrypt($data);
        return base64_encode($encryptedText);
    }

    /**
     * @param $symKey
     * @param Key $key
     * @return string
     */
    private function encryptSymmetricKeyWithAsymmetricKey($symKey, Key $key)
    {
        $this->crypterKey->loadKey($key->getKey());
        return base64_encode($this->crypterKey->encrypt($symKey));
    }

    /**
     * @param $encryptedSymKey
     * @param $encryptedText
     * @return string
     */
    private function encryptFinal($encryptedSymKey, $encryptedText)
    {
        $length = strlen($encryptedSymKey);
        // The first 3 bytes of the message are the key length
        $length = dechex($length);
        // Zero pad to be sure.
        $length = str_pad($length,3,'0',STR_PAD_LEFT);

        return $length.$encryptedSymKey.$encryptedText;
    }

    /**
     * @param $data
     * @return bool|string
     */
    private function extractEncryptedSymmetricKey($data)
    {
        $length = substr($data, 0, 3);
        $length = hexdec($length);
        return base64_decode(substr($data, 3, $length));
    }

    /**
     * @param $data
     * @return bool|string
     */
    private function extractEncryptedMessage($data)
    {
        $length = substr($data, 0, 3);
        $length = hexdec($length);

        $data = substr($data,3);
        $encryptedText = substr($data, $length);
        return base64_decode($encryptedText);
    }
}