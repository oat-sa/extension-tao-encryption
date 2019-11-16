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
namespace oat\taoDeliveryRdf\test\integration\model\import\assemblerFileReader;


use GuzzleHttp\Psr7\Stream;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\File;
use oat\taoEncryption\Service\DeliveryAssembly\import\assemblerDataProviders\EncryptedFileReader;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use tao_models_classes_service_StorageDirectory;

class EncryptedAssemblerFileReaderTest extends TestCase
{
    public function testStream()
    {
        $encryptionServiceMock = $this->createMock(EncryptionServiceInterface::class);
        $encryptionServiceMock->method('encrypt')->willReturnCallback(static function ($content) {
            return 'encrypted_'.$content;
        });
        $reader = new EncryptedFileReader([
            EncryptedFileReader::OPTION_ENCRYPTION_SERVICE => $encryptionServiceMock
        ]);
        /** @var File|MockObject $file */
        $file = $this->createMock(File::class);
        $streamMock = $this->createMock(Stream::class);
        $streamMock->method('getContents')->willReturn('content');
        $file->method('readPsrStream')->willReturn($streamMock);
        /** @var tao_models_classes_service_StorageDirectory|MockObject $directory */
        $directory = $this->createMock(tao_models_classes_service_StorageDirectory::class);

        $stream = $reader->getFileStream($file, $directory);
        $this->assertSame('encrypted_content', $stream->getContents());
        $this->assertSame($file, $reader->getFile());

        $reader->clean();
        $this->assertNull($reader->getFile());
        $reader->clean();
        $reader->clean();
    }
}
