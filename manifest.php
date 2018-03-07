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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\taoEncryption\scripts\install\RegisterDecryptResultStorage;
use oat\taoEncryption\scripts\install\RegisterEncryptionAsymmetricService;
use oat\taoEncryption\scripts\install\RegisterEncryptionSymmetricService;
use oat\taoEncryption\scripts\install\RegisterEncryptResultStorage;
use oat\taoEncryption\scripts\install\RegisterKeyPairProviderService;

return array(
    'name' => 'taoEncryption',
    'label' => 'TAO encryption',
    'description' => 'TAO encryption',
    'license' => 'GPL-2.0',
    'version' => '0.3.0',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'tao' => '>=17.7.0',
        'taoResultServer' => '>=6.2.0'
    ),

    'install' => array(
        'php' => [
            RegisterKeyPairProviderService::class,
            RegisterEncryptionAsymmetricService::class,
            RegisterEncryptionSymmetricService::class,
            RegisterEncryptResultStorage::class,
            RegisterDecryptResultStorage::class,
        ]
    ),
    'uninstall' => array(
    ),
    'update' => \oat\taoEncryption\scripts\update\Updater::class,
);
