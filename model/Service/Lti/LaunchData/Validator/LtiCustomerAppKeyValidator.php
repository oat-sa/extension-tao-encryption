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

namespace oat\taoEncryption\Service\Lti\LaunchData\Validator;

use oat\taoEncryption\Service\Session\EncryptedLtiUser;
use oat\taoLti\models\classes\LaunchData\Validator\Lti11LaunchDataValidator;
use oat\taoLti\models\classes\LtiInvalidLaunchDataException;
use oat\taoLti\models\classes\LtiLaunchData;

class LtiCustomerAppKeyValidator extends Lti11LaunchDataValidator
{
    /**
     * @param LtiLaunchData $data
     * @return bool
     * @throws LtiInvalidLaunchDataException
     * @throws \oat\taoLti\models\classes\LtiException
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function validate(LtiLaunchData $data)
    {
        if (!$this->isCustomerAppKeyValid($data)) {
            throw new LtiInvalidLaunchDataException('Customer App Key needs to be set.');
        }

        return parent::validate($data);
    }

    /**
     * @param LtiLaunchData $launchData
     * @return bool
     */
    private function isCustomerAppKeyValid(LtiLaunchData $launchData)
    {
        return $launchData->hasVariable(EncryptedLtiUser::PARAM_CUSTOM_CUSTOMER_APP_KEY);
    }
}