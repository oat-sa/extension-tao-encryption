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

namespace oat\taoEncryption\Service\Lti\LaunchData;

use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoLti\models\classes\LtiLaunchData;

class EncryptLaunchDataService extends ConfigurableService
{
    const SERVICE_ID = 'taoEncryption/EncryptLtiLaunchData';

    const OPTION_ALGORITHM = 'algorithm';

    const OPTION_KEY_PROVIDER = 'key_provider';

    /** @var AlgorithmSymmetricService */
    private $symmetricEncryption;

    /**
     * @param EncryptedLtiLaunchData $launchData
     * @return string
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     * @throws \Exception
     */
    public function encrypt(EncryptedLtiLaunchData $launchData)
    {
        $appKey = $launchData->getApplicationKey();

        return base64_encode($this->getEncryptionService($appKey)->encrypt(json_encode($launchData)));
    }

    /**
     * @param $encrypted
     * @param $appKey
     * @return EncryptedLtiLaunchData
     * @throws \Exception
     */
    public function decrypt($encrypted, $appKey)
    {
        $data = $this->getEncryptionService($appKey)->decrypt(base64_decode($encrypted));

        if (
            is_array($data = json_decode($data, true))
            && json_last_error() === JSON_ERROR_NONE
            && is_array($data)
        ) {
            $launchData = new LtiLaunchData(
                $data['variables'],
                $data['customParams']
            );
            return new EncryptedLtiLaunchData($launchData, $appKey);
        }

        throw new \Exception('Json Decode of lti launch data failed.');
    }

    /**
     * @param $applicationKey
     * @return array|AlgorithmSymmetricService|object
     */
    protected function getEncryptionService($applicationKey)
    {
        if (is_null($this->symmetricEncryption)) {

            $this->symmetricEncryption = $this->getServiceLocator()->get($this->getOption(static::OPTION_ALGORITHM));
        }

        /** @var SimpleKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get($this->getOption(static::OPTION_KEY_PROVIDER));
        $keyProvider->setKey($applicationKey);

        $this->symmetricEncryption->setKeyProvider($keyProvider);

        return $this->symmetricEncryption;
    }


}