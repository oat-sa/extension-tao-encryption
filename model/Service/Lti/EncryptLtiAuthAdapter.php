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

namespace oat\taoEncryption\Service\Lti;

use common_user_auth_Adapter;
use oat\taoEncryption\Service\Session\EncryptedLtiUser;
use oat\taoLti\models\classes\LtiAuthAdapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EncryptLtiAuthAdapter implements \common_user_auth_Adapter, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var common_user_auth_Adapter */
    private $ltiAuthAdapter;

    /**
     * @param LtiAuthAdapter $ltiAuthAdapter
     */
    public function __construct(LtiAuthAdapter $ltiAuthAdapter)
    {
        $this->ltiAuthAdapter = $ltiAuthAdapter;
    }

    /**
     * @inheritdoc
     */
    public function authenticate()
    {
        $user = new EncryptedLtiUser($this->ltiAuthAdapter->authenticate());

        $user->setServiceLocator($this->getServiceLocator());

        return $user;
    }
}