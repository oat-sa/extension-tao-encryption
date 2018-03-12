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
use oat\taoEncryption\Model\Key;
use oat\taoEncryption\Service\Session\EncryptedUser;
use phpseclib\Crypt\Random;

class FileKeyProviderService extends SimpleKeyProviderService
{
    const SERVICE_ID = 'taoEncryption/symmetricFileKeyProvider';

    const OPTION_FILESYSTEM_ID = 'fileSystemId';

    /**
     * @return Key
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function getKey()
    {
        $key = parent::getKey();
        if (!is_null($key)){
            return $key;
        }

        $user = \common_session_SessionManager::getSession()->getUser();
        if($user instanceof EncryptedUser){
            $this->setKey($user->getApplicationKey());
            return parent::getKey();
        }

        $this->setKey('');

        return parent::getKey();
    }

    /**
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function generateAndSaveKey()
    {
        /** @var FileSystemService $fileSystem */
        $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        $fs = $fileSystem->getFileSystem($this->getOption(static::OPTION_FILESYSTEM_ID));
        $symKey = Random::string(150);

        return $fs->put('user_application.key', base64_encode($symKey));
    }

    /**
     * @return string
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function getKeyFromFileSystem()
    {
        /** @var FileSystemService $fileSystem */
        $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        $fs = $fileSystem->getFileSystem($this->getOption(static::OPTION_FILESYSTEM_ID));

        return (string) $fs->read('user_application.key');
    }
}