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
use oat\taoEncryption\Model\Asymmetric\AsymmetricRSAKeyPairProvider;
use oat\taoEncryption\Model\PrivateKey;
use oat\taoEncryption\Model\PublicKey;

class AsymmetricKeyPairProviderService extends ConfigurableService
{
    const SERVICE_ID = 'taoEncryption/asymmetricKeyPairProvider';

    const OPTION_FILE_SYSTEM_ID = 'fileSystemId';

    /** @var AsymmetricRSAKeyPairProvider */
    private $asymmetricKeyPair;

    /**
     * @return PublicKey
     */
    public function getPublicKey()
    {
        return $this->getKeyPairModel()->getPublicKey();
    }

    /**
     * @return PrivateKey
     */
    public function getPrivateKey()
    {
        return $this->getKeyPairModel()->getPrivateKey();
    }

    /**
     * @return AsymmetricRSAKeyPairProvider
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
}