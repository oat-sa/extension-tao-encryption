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
namespace oat\taoEncryption\Test\Service\Sync;


use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\taoEncryption\Rdf\EncryptedUserRdf;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\Sync\EncryptUserSyncFormatter;
use oat\generis\test\TestCase;

class EncryptUserSyncFormatterTest extends TestCase
{
    protected $encryptString;

    public function setUp(): void
    {
        parent::setUp();

        $this->encryptString = base64_encode('encrypted');
    }

    /**
     * @throws \Exception
     */
    public function testDecryptProperties()
    {
        $service = $this->getService();

        $values = $service->decryptProperties([
            'not_encrypt_property' => 'do not encrypted',
            EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => 'some key',
            OntologyRdfs::RDFS_LABEL => $this->encryptString,
            GenerisRdf::PROPERTY_USER_FIRSTNAME => $this->encryptString,
            GenerisRdf::PROPERTY_USER_LASTNAME => $this->encryptString,
            GenerisRdf::PROPERTY_USER_ROLES => [
                $this->encryptString, $this->encryptString
            ],
        ]);

        $this->assertIsArray( $values);
        $this->assertSame('do not encrypted', (string)$values['not_encrypt_property']);
        $this->assertSame('decrypted', (string)$values[OntologyRdfs::RDFS_LABEL]);
        $this->assertSame('decrypted', (string)$values[GenerisRdf::PROPERTY_USER_FIRSTNAME]);
        $this->assertSame('decrypted', (string)$values[GenerisRdf::PROPERTY_USER_LASTNAME]);
        $this->assertIsArray( $values[GenerisRdf::PROPERTY_USER_ROLES]);
        $this->assertSame('decrypted', (string)$values[GenerisRdf::PROPERTY_USER_ROLES][0]);
        $this->assertSame('decrypted', (string)$values[GenerisRdf::PROPERTY_USER_ROLES][1]);
    }

    /**
     * @throws \Exception
     */
    public function testNoKeyPassToDecrypt()
    {
        $service = $this->getService();

        $values = $service->decryptProperties([
            'not_encrypt_property' => 'do not encrypted',
            OntologyRdfs::RDFS_LABEL => $this->encryptString,
        ]);

        $this->assertIsArray( $values);
        $this->assertSame('do not encrypted', (string)$values['not_encrypt_property']);
        $this->assertSame($this->encryptString, (string)$values[OntologyRdfs::RDFS_LABEL]);
    }

    /**
     * @throws \Exception
     */
    public function testEncryptProperties()
    {
        $service = $this->getService();
        $values = $service->encryptProperties(
            [
                'not_encrypt_property' => 'do not encrypt',
                EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => 'some key',
                OntologyRdfs::RDFS_LABEL => 'to_encrypt',
                GenerisRdf::PROPERTY_USER_FIRSTNAME => 'to_encrypt',
                GenerisRdf::PROPERTY_USER_LASTNAME => 'to_encrypt',
                GenerisRdf::PROPERTY_USER_ROLES => [
                    'role1', 'role2'
                ],
            ]
        );

        $this->assertIsArray( $values);
        $this->assertSame('do not encrypt', (string)$values['not_encrypt_property']);
        $this->assertSame($this->encryptString, (string)$values[OntologyRdfs::RDFS_LABEL]);
        $this->assertSame($this->encryptString, (string)$values[GenerisRdf::PROPERTY_USER_FIRSTNAME]);
        $this->assertSame($this->encryptString, (string)$values[GenerisRdf::PROPERTY_USER_LASTNAME]);
        $this->assertIsArray( $values[GenerisRdf::PROPERTY_USER_ROLES]);
        $this->assertSame($this->encryptString, (string)$values[GenerisRdf::PROPERTY_USER_ROLES][0]);
        $this->assertSame($this->encryptString, (string)$values[GenerisRdf::PROPERTY_USER_ROLES][1]);
    }

    /**
     * @throws \Exception
     */
    public function testNoKeyPassToEncrypt()
    {
        $service = $this->getService();

        $values = $service->encryptProperties([
            'not_encrypt_property' => 'do not encrypted',
            OntologyRdfs::RDFS_LABEL => 'value1',
        ]);

        $this->assertIsArray( $values);
        $this->assertSame('do not encrypted', (string)$values['not_encrypt_property']);
        $this->assertSame('value1', (string)$values[OntologyRdfs::RDFS_LABEL]);
    }

    /**
     * @throws \Exception
     */
    public function testFilterProperties()
    {
        $service = $this->getService();
        $values = $service->filterProperties( [
            'not_encrypt_property' => 'do not encrypt',
            EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => 'some key',
            OntologyRdfs::RDFS_LABEL => 'to_encrypt',
        ]);

        $this->assertIsArray( $values);
        $this->assertSame('do not encrypt', (string)$values['not_encrypt_property']);
        $this->assertSame($this->encryptString, (string)$values[OntologyRdfs::RDFS_LABEL]);
    }

    /**
     * @return EncryptUserSyncFormatter
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(EncryptUserSyncFormatter::class)
            ->setMethods(['getEncryptedProperties', 'getEncryptionService', 'callParentFilterProperties'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $service
            ->method('getEncryptedProperties')
            ->willReturn([
                OntologyRdfs::RDFS_LABEL,
                GenerisRdf::PROPERTY_USER_FIRSTNAME,
                GenerisRdf::PROPERTY_USER_LASTNAME,
                GenerisRdf::PROPERTY_USER_ROLES,
            ]);

        $service
            ->method('callParentFilterProperties')
            ->willReturn([
                'not_encrypt_property' => 'do not encrypt',
                EncryptedUserRdf::PROPERTY_ENCRYPTION_KEY => 'some key',
                OntologyRdfs::RDFS_LABEL => 'to_encrypt',
                GenerisRdf::PROPERTY_USER_FIRSTNAME => 'to_encrypt',
                GenerisRdf::PROPERTY_USER_LASTNAME => 'to_encrypt',
                GenerisRdf::PROPERTY_USER_ROLES => [
                    'role1', 'role2'
                ],
            ]);

        $service
            ->method('getEncryptionService')
            ->willReturn($this->mockEncryptionService());

        return $service;
    }

    protected function mockEncryptionService()
    {
        $encryption = $this->getMockBuilder(EncryptionSymmetricService::class)->getMock();
        $encryption
            ->method('encrypt')
            ->willReturn('encrypted');
        $encryption
            ->method('decrypt')
            ->willReturn('decrypted');

        return $encryption;
    }
}
