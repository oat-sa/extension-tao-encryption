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

use common_http_Request;
use common_user_auth_Adapter;
use oat\taoLti\models\classes\FactoryLtiAuthAdapterService;

class EncryptFactoryLtiAuthAdapterService extends FactoryLtiAuthAdapterService
{
    /**
     * @param common_http_Request $request
     * @return common_user_auth_Adapter
     */
    public function create(common_http_Request $request)
    {
        $adapter = $this->callParentCreate($request);
        $class = new EncryptLtiAuthAdapter($adapter);
        $this->propagate($class);

        return $class;
    }

    /**
     * @param common_http_Request $request
     * @return common_user_auth_Adapter
     */
    protected function callParentCreate(common_http_Request $request)
    {
        return parent::create($request);
    }
}