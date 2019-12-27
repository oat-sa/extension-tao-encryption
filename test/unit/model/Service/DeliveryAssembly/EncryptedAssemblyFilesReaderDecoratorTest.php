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

namespace oat\taoEncryption\test\unit\model\Service\DeliveryAssembly;

use Generator;
use ArrayIterator;
use Psr\Http\Message\StreamInterface;
use tao_models_classes_service_StorageDirectory;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\taoDeliveryRdf\model\assembly\AssemblyFilesReaderInterface;
use oat\taoEncryption\Service\DeliveryAssembly\EncryptedAssemblyFilesReaderDecorator;
use oat\taoEncryption\Service\EncryptionServiceInterface;

class EncryptedAssemblyFilesReaderDecoratorTest extends TestCase
{
    /**
     * @var EncryptedAssemblyFilesReaderDecorator
     */
    private $object;

    /**
     * @var AssemblyFilesReaderInterface|MockObject
     */
    private $filesReaderMock;

    /**
     * @var EncryptionServiceInterface|MockObject
     */
    private $encryptionServiceMock;

    /**
     * @var tao_models_classes_service_StorageDirectory|MockObject
     */
    private $directoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->directoryMock = $this->createMock(tao_models_classes_service_StorageDirectory::class);
        $this->encryptionServiceMock = $this->createMock(EncryptionServiceInterface::class);
        $this->filesReaderMock = $this->createMock(AssemblyFilesReaderInterface::class);

        $this->object = new EncryptedAssemblyFilesReaderDecorator($this->filesReaderMock, $this->encryptionServiceMock);
    }

    public function testGetFilesPublicDirectory()
    {
        $file1 = ['path' => 'filePath1', 'content' => 'CONTENT1'];
        $file2 = ['path' => 'filePath2', 'content' => 'CONTENT2'];

        $filesIterator = $this->getFilesIterator([$file1, $file2]);
        $this->filesReaderMock->method('getFiles')->willReturn($filesIterator);
        $this->directoryMock->method('isPublic')->willReturn(true);

        $result = $this->object->getFiles($this->directoryMock);

        $this->assertInstanceOf(Generator::class, $result);

        // Assert method returns correct values
        $fileStream = $result->current();
        $this->assertEquals($file1['path'], $result->key(), 'Returned file path must be as expected.');
        $this->assertInstanceOf(StreamInterface::class, $fileStream);
        $this->assertEquals($file1['content'], $fileStream->getContents(), 'Stream resource must return correct content.');
        $result->next();

        $fileStream2 = $result->current();
        $this->assertEquals($file2['path'], $result->key(), 'Returned file path must be as expected.');
        $this->assertInstanceOf(StreamInterface::class, $fileStream);
        $this->assertEquals($file2['content'], $fileStream2->getContents(), 'Stream resource must return correct content.');
        $result->next();

        $this->assertFalse($result->valid(), 'Files iterator must return correct number of results.');
    }

    public function testGetFilesPrivateDirectory()
    {
        $file1 = ['path' => 'filePath1', 'content' => 'CONTENT1'];
        $file2 = ['path' => 'filePath2', 'content' => 'CONTENT2'];
        $encryptedContent1 = 'ENCRYPTED_CONTENT1';
        $encryptedContent2 = 'ENCRYPTED_CONTENT2';

        $this->encryptionServiceMock->method('encrypt')
            ->willReturnMap([
                [$file1['content'], $encryptedContent1],
                [$file2['content'], $encryptedContent2]
            ]);

        $filesIterator = $this->getFilesIterator([$file1, $file2]);
        $this->filesReaderMock->method('getFiles')->willReturn($filesIterator);
        $this->directoryMock->method('isPublic')->willReturn(false);

        $filesIterator = $this->object->getFiles($this->directoryMock);

        $this->assertInstanceOf(Generator::class, $filesIterator);

        $fileStream = $filesIterator->current();
        $this->assertEquals($file1['path'], $filesIterator->key(), 'Returned file path must be as expected.');
        $this->assertInstanceOf(StreamInterface::class, $fileStream);
        $this->assertEquals($encryptedContent1, $fileStream->getContents(), 'Stream resource must return correct content.');
        $filesIterator->next();

        $fileStream2 = $filesIterator->current();
        $this->assertEquals($file2['path'], $filesIterator->key(), 'Returned file path must be as expected.');
        $this->assertInstanceOf(StreamInterface::class, $fileStream);
        $this->assertEquals($encryptedContent2, $fileStream2->getContents(), 'Stream resource must return correct content.');
        $filesIterator->next();

        $this->assertFalse($filesIterator->valid(), 'Files iterator must return correct number of results.');
    }

    /**
     * @param array $files
     * @return ArrayIterator
     */
    private function getFilesIterator(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $streamMock = $this->createMock(StreamInterface::class);
            $streamMock->method('getContents')
                ->willReturn($file['content']);

            $result[$file['path']] = $streamMock;
        }

        return new ArrayIterator($result);
    }
}
