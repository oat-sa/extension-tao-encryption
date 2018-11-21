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

use oat\taoEncryption\Service\Algorithm\AlgorithmSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoLti\models\classes\LtiLaunchData;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EncryptedLtiLaunchData extends LtiLaunchData implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var LtiLaunchData  */
    private $ltiLaunchData;

    /** @var AlgorithmSymmetricService */
    private $symmetricEncryption;

    /** @var string */
    private $applicationKey;

    /** @var bool */
    private $shouldEncrypt;

    /**
     * Spawns an LtiSession
     * @param LtiLaunchData $ltiLaunchData
     * @param $applicationKey
     * @param bool $shouldEncrypt
     */
    public function __construct(LtiLaunchData $ltiLaunchData, $applicationKey, $shouldEncrypt = true)
    {
        $this->ltiLaunchData = $ltiLaunchData;
        $this->applicationKey = $applicationKey;
        $this->shouldEncrypt = $shouldEncrypt;
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserGivenName()
    {
        $data = $this->ltiLaunchData->getUserGivenName();

        return $this->encrypt($data);
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserFamilyName()
    {
        $data = $this->ltiLaunchData->getUserFamilyName();

        return $this->encrypt($data);
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserFullName()
    {
        $data =  $this->ltiLaunchData->getUserFullName();

        return $this->encrypt($data);
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserEmail()
    {
        $data = $this->ltiLaunchData->getUserEmail();

        return $this->encrypt($data);
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserRoles()
    {
        return $this->ltiLaunchData->getUserRoles();
    }

    public function getCustomParameter($key)
    {
        return $this->ltiLaunchData->getCustomParameter($key);
    }

    public function getCustomParameters()
    {
        return $this->ltiLaunchData->getCustomParameters();
    }

    public function getVariables()
    {
        return $this->ltiLaunchData->getVariables();
    }

    public function getResourceLinkID()
    {
        return $this->ltiLaunchData->getResourceLinkID();
    }

    public function getVariable($key)
    {
        return $this->ltiLaunchData->getVariable($key);
    }

    public function getResourceLinkTitle()
    {
        return $this->ltiLaunchData->getResourceLinkTitle();
    }

    public function hasVariable($key)
    {
        return $this->ltiLaunchData->hasVariable($key);
    }

    /**
     * @return mixed|string
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     * @throws \Exception
     */
    public function getUserID()
    {
        return $this->ltiLaunchData->getUserID();
    }

    public function hasLaunchLanguage()
    {
        return $this->ltiLaunchData->hasLaunchLanguage();
    }

    public function getLaunchLanguage()
    {
        return $this->ltiLaunchData->getLaunchLanguage();
    }

    public function getToolConsumerName()
    {
        return $this->ltiLaunchData->getToolConsumerName();
    }

    public function getLtiConsumer()
    {
        return $this->ltiLaunchData->getLtiConsumer();
    }

    public function getOauthKey()
    {
        return $this->ltiLaunchData->getOauthKey();
    }

    public function hasReturnUrl()
    {
        return $this->ltiLaunchData->hasReturnUrl();
    }

    public function getReturnUrl()
    {
        return $this->ltiLaunchData->getReturnUrl();
    }

    /**
     * @return array|AlgorithmSymmetricService|object
     */
    public function getEncryptionService()
    {
        if (is_null($this->symmetricEncryption)) {
            $this->symmetricEncryption = $this->getServiceLocator()->get(AlgorithmSymmetricService::SERVICE_ID);

            /** @var SimpleKeyProviderService $keyProvider */
            $keyProvider = $this->getServiceLocator()->get(SimpleKeyProviderService::SERVICE_ID);
            $keyProvider->setKey($this->applicationKey);

            $this->symmetricEncryption->setKeyProvider($keyProvider);

        }

        return $this->symmetricEncryption;
    }

    /**
     * @return $this
     */
    public function disableEncryption()
    {
        $this->shouldEncrypt = false;

        return $this;
    }

    /**
     * @param $data
     * @return string
     * @throws \Exception
     */
    protected function encrypt($data)
    {
        if ($this->shouldEncrypt) {
            return base64_encode($this->getEncryptionService()->encrypt($data));
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getApplicationKey()
    {
        return $this->applicationKey;
    }

    /**
     * @return LtiLaunchData
     */
    public function getLaunchData()
    {
        return $this->ltiLaunchData;
    }
}