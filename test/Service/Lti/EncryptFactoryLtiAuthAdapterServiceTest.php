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

namespace oat\taoEncryption\Test\Service\Lti;

use common_ext_Extension;
use common_ext_ExtensionsManager;
use common_http_Request;
use oat\generis\test\TestCase;
use oat\taoEncryption\Service\Lti\EncryptFactoryLtiAuthAdapterService;
use oat\taoEncryption\Service\Lti\EncryptLtiAuthAdapter;
use Zend\ServiceManager\ServiceLocatorInterface;

class EncryptFactoryLtiAuthAdapterServiceTest extends TestCase
{
    public function testCreate()
    {
        $service = $this->mockService();

        $request = $this->getMockBuilder(common_http_Request::class)->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(EncryptLtiAuthAdapter::class, $service->create($request));
    }

    /**
     * @return EncryptFactoryLtiAuthAdapterService
     */
    protected function mockService()
    {
        $service = $this->getMockBuilder(EncryptFactoryLtiAuthAdapterService::class)->disableOriginalConstructor()
            ->setMethods(['propagate'])
            ->getMockForAbstractClass();

        $service->setServiceLocator($this->getServiceLocatorMock([
            common_ext_ExtensionsManager::SERVICE_ID => $this->mockExtensionManager()
        ]));

        return $service;
    }

    protected function mockExtensionManager()
    {
        $extension = $this->getMockBuilder(common_ext_Extension::class)->disableOriginalConstructor()->getMock();
        $extension
            ->method('getConfig')
            ->willReturn([
                'adapter' => 'oat\\taoLti\\models\\classes\\LtiAuthAdapter'
            ]);

        $manager = $this->getMockBuilder(common_ext_ExtensionsManager::class)->disableOriginalConstructor()->getMock();
        $manager
            ->method('getExtensionById')
            ->willReturn($extension);

        return $manager;
    }
}
