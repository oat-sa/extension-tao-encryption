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
namespace oat\taoEncryption\Test\integration\Service\Lti;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\taoEncryption\Service\Lti\EncryptLtiAuthAdapter;
use oat\taoEncryption\Service\Session\EncryptedLtiUser;
use oat\taoLti\models\classes\LtiAuthAdapter;
use oat\taoLti\models\classes\user\LtiUser;
use Zend\ServiceManager\ServiceLocatorInterface;

class EncryptLtiAuthAdapterTest extends GenerisPhpUnitTestRunner
{

    public function testAuthenticate()
    {
        $adapter = new EncryptLtiAuthAdapter($this->mockAdapter());
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $adapter->setServiceLocator($serviceLocator);

        $this->assertInstanceOf(EncryptedLtiUser::class, $adapter->authenticate());
    }

    /**
     * @return LtiAuthAdapter
     */
    protected function mockAdapter()
    {
        $ltiUser = $this->getMockBuilder(LtiUser::class)->disableOriginalConstructor()->getMock();
        $adapter = $this->getMockBuilder(LtiAuthAdapter::class)->disableOriginalConstructor()->getMock();
        $adapter
            ->method('authenticate')
            ->willReturn($ltiUser);

        return $adapter;
    }
}
