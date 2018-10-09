<?php

namespace oat\taoEncryption\Service\Session;

use oat\oatbox\service\ServiceManager;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\LtiConsumer\EncryptedLtiConsumer;
use oat\taoLti\models\classes\user\LtiUserInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EncryptLtiUser extends EncryptedUser implements LtiUserInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const PARAM_CUSTOM_CUSTOMER_APP_KEY = 'custom_customer_app_key';

    /** @var LtiUserInterface */
    protected $realUser;

    /**
     * @inheritdoc
     */
    public function getLaunchData()
    {
        return $this->realUser->getLaunchData();
    }

    /**
     * @return string
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     * @throws \common_Exception
     * @throws \core_kernel_classes_EmptyProperty
     * @throws \Exception
     */
    public function getApplicationKey()
    {
        if (is_null($this->applicationKey)) {
            $ltiConsumer = $this->realUser->getLaunchData()->getLtiConsumer();
            $value = $ltiConsumer->getUniquePropertyValue(
                new \core_kernel_classes_Property(EncryptedLtiConsumer::PROPERTY_ENCRYPTED_APPLICATION_KEY)
            );
            $appKey = $value->literal;

            $variables = $this->realUser->getLaunchData()->getVariables();
            if (!isset($variables[static::PARAM_CUSTOM_CUSTOMER_APP_KEY])) {
                throw new \common_Exception('Customer App Key needs to be set.');
            }

            $customerAppKey = $variables[static::PARAM_CUSTOM_CUSTOMER_APP_KEY];

            $this->applicationKey = $this->decryptAppKey($customerAppKey, $appKey);
        }

        return parent::getApplicationKey();
    }

    /**
     * @param $customerAppKey
     * @param $appKey
     * @return string
     * @throws \Exception
     */
    protected function decryptAppKey($customerAppKey, $appKey)
    {
        /** @var EncryptionSymmetricService $encryptService */
        $encryptService = $this->getServiceLocator()->get(EncryptionSymmetricService::SERVICE_ID);

        /** @var SimpleKeyProviderService $simpleKeyProvider */
        $simpleKeyProvider = $this->getServiceLocator()->get(SimpleKeyProviderService::SERVICE_ID);
        $simpleKeyProvider->setKey($customerAppKey);
        $encryptService->setKeyProvider($simpleKeyProvider);

        return $encryptService->decrypt(base64_decode($appKey));
    }

    public function __wakeup()
    {
        $this->setServiceLocator(ServiceManager::getServiceManager());
    }
}