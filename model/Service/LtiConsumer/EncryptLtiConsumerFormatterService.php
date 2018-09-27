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

namespace oat\taoEncryption\Service\LtiConsumer;

use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoSync\model\formatter\FormatterService;

class EncryptLtiConsumerFormatterService extends FormatterService
{
    const SERVICE_ID = 'taoEncryption/encryptLtiConsumer';

    const OPTION_ENCRYPTION_SERVICE = 'encryptionService';
    const OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE = 'keyProviderService';

    /** @var EncryptionSymmetricService */
    private $encryptionService;

    /**
     * @param array $triples
     * @param array $options
     * @param array $params
     * @return array
     * @throws \Exception
     */
    protected function filterProperties(array $triples, array $options = [], array $params = [])
    {
        $properties = $this->callParentFilterProperties($triples, $options, $params);

        if (empty($properties[EncryptedLtiConsumer::PROPERTY_CUSTOMER_APP_KEY])) {
            throw new \Exception('Customer Application Key not set to Lti Consumer');
        }

        $properties[EncryptedLtiConsumer::PROPERTY_ENCRYPTED_APPLICATION_KEY]
            = $this->encryptAppKey($properties[EncryptedLtiConsumer::PROPERTY_CUSTOMER_APP_KEY]);

        unset($properties[EncryptedLtiConsumer::PROPERTY_CUSTOMER_APP_KEY]);
        return $properties;
    }

    /**
     * @param string $customerAppKey
     * @return string
     * @throws \Exception
     */
    protected function encryptAppKey($customerAppKey)
    {
        /** @var SimpleKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get(SimpleKeyProviderService::SERVICE_ID);
        $keyProvider->setKey($customerAppKey);

        $this->getEncryptionService()->setKeyProvider($keyProvider);

        return base64_encode($this->getEncryptionService()->encrypt($this->getApplicationKey()));
    }

    /**
     * @param array $triples
     * @param array $options
     * @param array $params
     * @return array
     */
    protected function callParentFilterProperties(array $triples, array $options = [], array $params = [])
    {
        return parent::filterProperties($triples, $options, $params);
    }

    /**
     * @return array|EncryptionSymmetricService|object
     * @throws \Exception
     */
    protected function getEncryptionService()
    {
        if (is_null($this->encryptionService)){
            $service = $this->getServiceLocator()->get(
                $this->getOption(static::OPTION_ENCRYPTION_SERVICE)
            );
            if (!$service instanceof EncryptionSymmetricService) {
                throw new \Exception('Encryption Service must be instance of EncryptionSymmetricService');
            }

            $this->encryptionService = $service;
        }

        return $this->encryptionService;
    }


    /**
     * @return string
     */
    protected function getOptionEncryptionKeyProvider()
    {
        return $this->getOption(static::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getApplicationKey()
    {
        /** @var FileKeyProviderService $keyProvider */
        $keyProvider = $this->getServiceLocator()->get($this->getOptionEncryptionKeyProvider());

        return $keyProvider->getKeyFromFileSystem();
    }
}