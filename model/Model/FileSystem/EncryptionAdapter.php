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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoEncryption\Model\FileSystem;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Config;
use oat\taoEncryption\Service\EncryptionServiceInterface;

/**
 * Class EncryptionAdapter
 *
 * This Class implements a FlySystem Adapter able to deal with encryption. It is able to encrypt/decrypt
 * encrypted data on the file system using the appropriate injected EncryptionServiceInterface object.
 *
 * Please note that at the time being, this adapter works for local filesystems only.
 *
 * @package oat\taoEncryption\Model\FileSystem
 * @see EncryptionServiceInterface
 */
class EncryptionAdapter extends Local
{
    /**
     * Encryption Service Interface
     *
     * The EncryptionServiceInterface object to be used for encryption/decryption.
     *
     * @var EncryptionServiceInterface
     */
    private $encryptionService;

    /**
     * EncryptionAdapter constructor.
     *
     * Create a new Encryption FlySystem Adapter.
     *
     * @param $root The root path on which the adapter operates.
     * @param EncryptionServiceInterface $encryptionService
     * @param int $writeFlags
     * @param int $linkHandling
     * @param array $permissions
     */
    public function __construct($root, EncryptionServiceInterface $encryptionService, $writeFlags = LOCK_EX, $linkHandling = self::DISALLOW_LINKS, array $permissions = [])
    {
        parent::__construct($root, $writeFlags, $linkHandling, $permissions);
        $this->encryptionService = $encryptionService;
    }


    public function write($path, $contents, Config $config)
    {
        $contents = $this->encryptionService->encrypt($contents);
        return parent::write($path, $contents, $config);
    }

    public function writeStream($path, $resource, Config $config)
    {
        $contents = $this->encryptionService->encrypt(stream_get_contents($resource));
        $fp = fopen('php://temp','r+');
        fwrite($fp, $contents);
        rewind($fp);

        return parent::writeStream($path, $fp, $config);
    }

    public function update($path, $contents, Config $config)
    {
        $contents = $this->encryptionService->encrypt($contents);
        return parent::update($path, $contents, $config);
    }

    public function updateStream($path, $resource, Config $config)
    {
        $contents = $this->encryptionService->encrypt(stream_get_contents($resource));
        $fp = fopen('php://temp','r+');
        fwrite($fp, $contents);
        rewind($fp);
        return parent::update($path, $contents, $config);
    }

    public function read($path)
    {
        $contents = parent::read($path);
        $contents['contents'] = $this->encryptionService->decrypt($contents['contents']);

        return $contents;
    }

    public function readStream($path)
    {
        $stream = parent::readStream($path);
        $fp = fopen('php://temp','r+');
        fwrite($fp, $this->encryptionService->decrypt(stream_get_contents($stream['stream'])));
        rewind($fp);
        $stream['stream'] = $fp;

        return $stream;
    }
}