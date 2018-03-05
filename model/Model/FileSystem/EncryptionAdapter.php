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

class EncryptionAdapter extends Local
{
    private $encryptionService;

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
        return parent::writeStream($path, $contents, $config);
    }

    public function update($path, $contents, Config $config)
    {
        $contents = $this->encryptionService->encrypt($contents);
        return parent::update($path, $contents, $config);
    }

    public function updateStream($path, $resource, Config $config)
    {
        $contents = $this->encryptionService->encrypt(stream_get_contents($resource));
        return parent::update($path, $contents, $config);
    }

    public function read($path)
    {
        $contents = parent::read($path);
        return $this->encryptionService->decrypt($contents);

    }

    public function readStream($path)
    {
        $stream = parent::readStream($path);
        $fp = fopen('php://memory','r+');

        fwrite($fp, stream_get_contents($stream));
        rewind($fp);

        return $fp;
    }
}