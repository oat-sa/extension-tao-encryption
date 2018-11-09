<?php

namespace oat\taoEncryption\Service\Session;

use common_user_User;
use oat\oatbox\service\ServiceManager;
use oat\taoEncryption\Service\EncryptionSymmetricService;
use oat\taoEncryption\Service\KeyProvider\SimpleKeyProviderService;
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchData;
use oat\taoEncryption\Service\LtiConsumer\EncryptedLtiConsumer;
use oat\taoLti\models\classes\user\LtiUserInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EncryptedLtiUser extends EncryptedUser implements LtiUserInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const PARAM_CUSTOM_CUSTOMER_APP_KEY = 'custom_customer_app_key';

    /** @var LtiUserInterface */
    protected $realUser;

    /**
     * EncryptedUser constructor.
     * @param common_user_User $user
     * @param null $hashForEncryption
     * @throws \Exception
     */
    public function __construct(common_user_User $user, $hashForEncryption = null)
    {
        $user = $this->switchEncryptionOff($user);

        parent::__construct($user, $hashForEncryption);
    }

    /**
     * @inheritdoc
     */
    public function getLaunchData()
    {
        return $this->realUser->getLaunchData();
    }

    /**
     * @param $property
     * @return array
     * @throws \Exception
     */
    public function getPropertyValues($property)
    {
        $values = $this->realUser->getPropertyValues($property);

        return $values;
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
            $ltiConsumer = $this->getLaunchData()->getLtiConsumer();
            $value = $ltiConsumer->getUniquePropertyValue(
                new \core_kernel_classes_Property(EncryptedLtiConsumer::PROPERTY_ENCRYPTED_APPLICATION_KEY)
            );
            $appKey = $value->literal;
            $launchData = $this->getLaunchData();
            if (!$launchData->hasVariable(static::PARAM_CUSTOM_CUSTOMER_APP_KEY)) {
                throw new \common_Exception('Customer App Key needs to be set.');
            }

            $this->applicationKey = $this->decryptAppKey($launchData->getVariable(static::PARAM_CUSTOM_CUSTOMER_APP_KEY), $appKey);
        }

        return parent::getApplicationKey();
    }

    /**
     * @param string $userId
     * @return mixed|void
     */
    public function setIdentifier($userId)
    {
        $this->realUser->setIdentifier($userId);
    }

    public function __wakeup()
    {
        $this->setServiceLocator(ServiceManager::getServiceManager());
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

    /**
     * @param common_user_User $user
     * @return common_user_User
     */
    protected function switchEncryptionOff(common_user_User $user)
    {
        if ($user instanceof LtiUserInterface) {
            $launchData = $user->getLaunchData();

            if ($launchData instanceof EncryptedLtiLaunchData) {
                $launchData->disableEncryption();
            }
        }

        return $user;
    }
}