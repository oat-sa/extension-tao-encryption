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
namespace oat\taoEncryption\Test\Service\DeliveryMonitoring;

use oat\taoEncryption\Model\FileSystem\EncryptionAdapter;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\FileSystem\EncryptionFlyWrapper;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;

class EncryptionFlyWrapperTest extends TestCase
{
    public function testGetAdapter()
    {
        $symService = $this->getMockBuilder(EncryptionSymmetricService::class)->disableOriginalConstructor()->getMock();
        $keyProvider = $this->getMockBuilder(FileKeyProviderService::class)->disableOriginalConstructor()->getMock();
        $algorithmService = $this->getMockBuilder(AlgorithmSymmetricService::class)->disableOriginalConstructor()->getMock();

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')
            ->willReturnOnConsecutiveCalls($symService, $keyProvider, $algorithmService);

        /** @var EncryptionFlyWrapper $flyWrapper */
        $flyWrapper = $this->getMockBuilder(EncryptionFlyWrapper::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $flyWrapper->setOption(EncryptionFlyWrapper::OPTION_ROOT, __DIR__);
        $flyWrapper->setOption(EncryptionFlyWrapper::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE, FileKeyProviderService::SERVICE_ID);
        $flyWrapper->setOption(EncryptionFlyWrapper::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE, FileKeyProviderService::SERVICE_ID);
        $flyWrapper->setServiceLocator($serviceLocator);

        $this->assertInstanceOf(EncryptionAdapter::class, $flyWrapper->getAdapter());
    }
}
