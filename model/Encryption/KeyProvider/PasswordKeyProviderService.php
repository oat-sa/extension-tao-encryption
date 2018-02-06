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

namespace oat\taoEncryption\Encryption\KeyProvider;

use common_session_SessionManager;
use oat\generis\model\GenerisRdf;
use oat\taoEncryption\Model\Key;

class PasswordKeyProviderService extends SymmetricKeyProviderService
{
    const SERVICE_ID = 'taoEncryption/symmetricPasswordProvider';

    /**
     * @return Key
     * @throws \common_exception_Error
     */
    public function getKey()
    {
        $session = common_session_SessionManager::getSession();
        $password = $session->getUser()->getPropertyValues(GenerisRdf::PROPERTY_USER_PASSWORD);

        return new Key(base64_encode(json_encode($password))) ;
    }
}