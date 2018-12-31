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

namespace oat\taoEncryption\Service\KeyProvider;

use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\taoEncryption\controller\EncryptionApi;
use oat\taoEncryption\Model\Asymmetric\AsymmetricRSAKeyPairProvider;
use oat\taoEncryption\Model\PrivateKey;
use oat\taoEncryption\Model\PublicKey;
use oat\taoSync\model\event\DataSynchronisationStarted;

class AsymmetricKeyPairProviderService extends ConfigurableService
{
    const SERVICE_ID = 'taoEncryption/asymmetricKeyPairProvider';

    const OPTION_FILE_SYSTEM_ID = 'fileSystemId';

    /** @var AsymmetricRSAKeyPairProvider */
    private $asymmetricKeyPair;

    /**
     * @return PublicKey
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function getPublicKey()
    {
        return $this->getKeyPairModel()->getPublicKey();
    }

    /**
     * @return PrivateKey
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function getPrivateKey()
    {
        return $this->getKeyPairModel()->getPrivateKey();
    }

    /**
     * @return AsymmetricRSAKeyPairProvider
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function getKeyPairModel()
    {
        if (is_null($this->asymmetricKeyPair)) {
            /** @var FileSystemService $fileSystem */
            $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
            $fs = $fileSystem->getFileSystem($this->getOption(static::OPTION_FILE_SYSTEM_ID));

            $this->asymmetricKeyPair = new AsymmetricRSAKeyPairProvider($fs);
        }

        return $this->asymmetricKeyPair;
    }

    /**
     * Compare a checksum against a value
     *
     * @param $checksum
     * @param $value
     * @return bool
     */
    public function comparePublicKeyChecksum($checksum, $value)
    {
        return $checksum === $this->hash($value);
    }

    /**
     * Event listener for synchronisation start event
     *
     * @param DataSynchronisationStarted $event
     * @throws \common_Exception
     */
    static public function onSynchronisationStarted(DataSynchronisationStarted $event)
    {
        /** @var AsymmetricKeyPairProviderService $keyPairService */
        $keyPairService = ServiceManager::getServiceManager()->get(self::SERVICE_ID);
        return $keyPairService->synchronizePublicKey();
    }

    /**
     * Get hash of public key
     *
     * @return string
     */
    protected function getPublicKeyChecksum()
    {
        try {
            return $this->hash($this->getPublicKey()->getKey());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Hash a value with crc32 algorithm
     *
     * @param $value
     * @return string
     */
    protected function hash($value)
    {
        return hash('crc32', $value);
    }

    /**
     * Synchronize a local public against a remote host
     *
     * Send the local public key checksum to remote
     * Parse the response to know if an update is required
     * If yes then save the remote public from response
     *
     * @throws \common_Exception
     */
    protected function synchronizePublicKey()
    {
        $checksum = $this->getPublicKeyChecksum();

        /** @var KeyProviderClient $keyProviderClient */
        $keyProviderClient = $this->getServiceLocator()->get(KeyProviderClient::SERVICE_ID);
        $response = $keyProviderClient->updatePublicKey($checksum)->getContents();

        if (
            is_array($response = json_decode($response, true))
            && json_last_error() === JSON_ERROR_NONE
            && array_key_exists(EncryptionApi::PARAM_REQUIRE_UPDATE, $response)
            && array_key_exists(EncryptionApi::PARAM_PUBLIC_KEY, $response)
        ) {
            if ($response[EncryptionApi::PARAM_REQUIRE_UPDATE] === true) {
                $this->logNotice('Remote and local encryption keys does not match. Updating local public key...');
                $this->getKeyPairModel()->savePublicKey(new PublicKey($response[EncryptionApi::PARAM_PUBLIC_KEY]));
            } else {
                $this->logInfo('Remote and local encryption keys are already synchronized.');
            }
        } else {
            throw new \LogicException('The response is not correctly formatted. Process aborted.');
        }
    }
}