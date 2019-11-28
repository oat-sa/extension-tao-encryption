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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

namespace oat\taoEncryption\Service\DeliveryAssembly;

use oat\taoDeliveryRdf\model\export\AssemblyExporterService;
use tao_models_classes_service_StorageDirectory;
use oat\taoDeliveryRdf\model\export\AssemblyExportFailedException;
use oat\taoEncryption\Service\EncryptionAwareInterface;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use Psr\Http\Message\StreamInterface;

class EncryptedAssemblerService extends AssemblyExporterService implements EncryptionAwareInterface
{
    /**
     * @var EncryptionServiceInterface
     */
    private $encryptionService;

    /**
     * @param tao_models_classes_service_StorageDirectory $directory
     * @param string $file
     * @return StreamInterface|resource
     * @throws AssemblyExportFailedException
     */
    protected function getFileSource(tao_models_classes_service_StorageDirectory $directory,$file)
    {
        $stream = parent::getFileSource($directory, $file);
        if ($directory->isPublic()) {
            return $stream;
        }

        return $this->encryptStream($stream);
    }

    /**
     * TODO: switch to another encryption library which supports stream encryption and encrypt stream.
     *
     * @param StreamInterface $stream
     * @return resource|StreamInterface
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

        return $fp;
    }

    /**
     * @param EncryptionServiceInterface $encryptionService
     */
    public function setEncryptionService(EncryptionServiceInterface $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * @return EncryptionServiceInterface
     * @throws AssemblyExportFailedException
     */
    public function getEncryptionService()
    {
        if (!$this->encryptionService instanceof EncryptionServiceInterface) {
            throw new AssemblyExportFailedException('Encryption service is not set up.');
        }

        return $this->encryptionService;
    }
}
