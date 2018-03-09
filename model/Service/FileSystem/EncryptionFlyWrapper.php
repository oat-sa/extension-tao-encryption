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

namespace oat\taoEncryption\Service\FileSystem;

use League\Flysystem\AdapterInterface;
use oat\oatbox\filesystem\utils\FlyWrapperTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\Model\FileSystem\EncryptionAdapter;

/**
 * Class EncryptionFlyWrapper
 *
 * A Configurable Wrapper for the EncryptionAdapter FlySystem adapter.
 *
 * @package oat\taoEncryption\Service\FileSystem
 * @see EncryptionAdapter
 */
class EncryptionFlyWrapper extends ConfigurableService implements AdapterInterface
{
    use FlyWrapperTrait;

    const OPTION_ENCRYPTIONSERVICEID = 'encryptionServiceId';
    const OPTION_ENCRYPTIONKEYPROVIDERSERVICE = 'keyProviderService';
    const OPTION_ROOT = 'root';

    /**
     * Get Adapter
     *
     * Returns the actual underlying adapter in use for this wrapper.
     *
     * @return EncryptionAdapter
     */
    public function getAdapter()
    {
        $encryptionService = $this->getServiceLocator()->get($this->getOption(self::OPTION_ENCRYPTIONSERVICEID));

        if ($this->hasOption(self::OPTION_ENCRYPTIONKEYPROVIDERSERVICE)) {
            /** @var \oat\taoEncryption\Service\KeyProvider\SymmetricKeyProviderService $keyProvider */
            $keyProvider = $this->getServiceLocator()->get($this->getOption(self::OPTION_ENCRYPTION_KEY_PROVIDER_SERVICE));
            $encryptionService->setKeyProvider($keyProvider);
        }

        return new EncryptionAdapter(
            $this->getOption(self::OPTION_ROOT),
            $encryptionService
        );
    }
}