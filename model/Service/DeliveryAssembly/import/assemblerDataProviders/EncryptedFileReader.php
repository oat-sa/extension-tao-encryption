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
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoEncryption\Service\DeliveryAssembly\import\assemblerDataProviders;


use GuzzleHttp\Psr7\Stream;
use oat\oatbox\filesystem\File;
use oat\taoDeliveryRdf\model\export\AssemblyExportFailedException;
use oat\taoDeliveryRdf\model\import\assemblerDataProviders\AssemblerFileReaderAbstract;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use Psr\Http\Message\StreamInterface;
use tao_models_classes_service_StorageDirectory;

class EncryptedFileReader extends AssemblerFileReaderAbstract
{
    const OPTION_ENCRYPTION_SERVICE = 'encryptionService';

    /**
     * @param File $file
     * @param tao_models_classes_service_StorageDirectory $directory
     * @return Stream|StreamInterface|resource
     * @throws AssemblyExportFailedException
     */
    protected function stream(File $file, tao_models_classes_service_StorageDirectory $directory)
    {
        return $directory->isPublic()
            ? $file->readPsrStream()
            : $this->encryptStream($file->readPsrStream());
    }

    /**
     * TODO: switch to another encryption library which supports stream encryption and encrypt stream.
     *
     * @param StreamInterface $stream
     * @return StreamInterface
     * @throws AssemblyExportFailedException
     */
    private function encryptStream(StreamInterface $stream)
    {
        $encryptionService = $this->getEncryptionService();

        $cont = $stream->getContents();
        $contents = $encryptionService->encrypt($cont);
        $fp = fopen('php://temp','r+');
        fwrite($fp, $contents);
        rewind($fp);

        return new Stream($fp);
    }

    /**
     * @return EncryptionServiceInterface
     * @throws AssemblyExportFailedException
     */
    public function getEncryptionService()
    {
        if ($this->hasOption(self::OPTION_ENCRYPTION_SERVICE) && $this->getOption(self::OPTION_ENCRYPTION_SERVICE) instanceof EncryptionServiceInterface) {
            return $this->getOption(self::OPTION_ENCRYPTION_SERVICE);
        }

        throw new AssemblyExportFailedException('Encryption service is not set up.');
    }
}
