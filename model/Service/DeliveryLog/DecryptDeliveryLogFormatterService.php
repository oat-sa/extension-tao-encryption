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

namespace oat\taoEncryption\Service\DeliveryLog;

use oat\oatbox\log\LoggerAwareTrait;
use oat\taoEncryption\Service\EncryptionSymmetricServiceHelper;
use oat\taoEncryption\Service\KeyProvider\FileKeyProviderService;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoSync\model\DeliveryLog\DeliveryLogFormatterService;

class DecryptDeliveryLogFormatterService extends DeliveryLogFormatterService
{
    use LoggerAwareTrait;
    use EncryptionSymmetricServiceHelper;

    const OPTION_ENCRYPTION_SERVICE = 'symmetricEncryptionService';

    const OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE = 'keyProviderService';

    /**
     * @param array $deliveryLog
     * @return array
     * @throws \Exception
     */
    public function format(array $deliveryLog)
    {
        $data = $deliveryLog[DeliveryLog::DATA];
        $data = $this->getEncryptionService($this->getApplicationKey())->decrypt(base64_decode($data));

        $deliveryLog[DeliveryLog::DATA] = $data;

        return $deliveryLog;
    }

    /**
     * @return string
     */
    protected function getOptionEncryptionService()
    {
        return $this->getOption(static::OPTION_ENCRYPTION_SERVICE);
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