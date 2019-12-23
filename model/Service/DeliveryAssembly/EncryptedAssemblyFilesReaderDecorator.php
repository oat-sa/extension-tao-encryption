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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 */

namespace oat\taoEncryption\Service\DeliveryAssembly;

use Generator;
use GuzzleHttp\Psr7\Stream;
use oat\taoDeliveryRdf\model\assembly\AssemblyFilesReaderInterface;
use oat\taoDeliveryRdf\model\assembly\CompiledTestConverterService;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use Psr\Http\Message\StreamInterface;
use tao_models_classes_service_StorageDirectory;

class EncryptedAssemblyFilesReaderDecorator implements AssemblyFilesReaderInterface
{
    /**
     * @var AssemblyFilesReaderInterface
     */
    private $filesReader;

    /**
     * @var EncryptionServiceInterface
     */
    private $encryptionService;

    /**
     * @param AssemblyFilesReaderInterface  $filesReader
     * @param EncryptionServiceInterface    $encryptionService
     */
    public function __construct(AssemblyFilesReaderInterface $filesReader, EncryptionServiceInterface $encryptionService)
    {
        $this->filesReader = $filesReader;
        $this->encryptionService = $encryptionService;
    }

    /**
     * @param CompiledTestConverterService $compiledTestConverter
     */
    public function setCompiledTestConverter(CompiledTestConverterService $compiledTestConverter)
    {
        $this->filesReader->setCompiledTestConverter($compiledTestConverter);
    }

    /**
     * @param tao_models_classes_service_StorageDirectory $directory
     *
     * @return Generator In format: [string $filePath => StreamInterface $fileStream]
     */
    public function getFiles(tao_models_classes_service_StorageDirectory $directory)
    {
        if ($directory->isPublic()) {
            yield from $this->filesReader->getFiles($directory);
            return;
        }

        foreach ($this->filesReader->getFiles($directory) as $filePath => $fileStream) {
            yield $filePath => $this->encryptStream($fileStream);
        }
    }

    /**
     * TODO: switch to another encryption library which supports stream encryption
     *
     * @param StreamInterface $fileStream
     * @return StreamInterface
     */
    private function encryptStream(StreamInterface $fileStream)
    {
        $streamContent = $fileStream->getContents();
        $encryptedContent = $this->encryptionService->encrypt($streamContent);
        $fp = fopen('php://temp','r+');
        fwrite($fp, $encryptedContent);
        rewind($fp);

        return new Stream($fp);
    }
}
