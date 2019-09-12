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
 * Copyright (c) 2017-2018 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\taoEncryption\scripts\install\RegisterDecryptResultStorage;
use oat\taoEncryption\scripts\install\RegisterEncryptionAsymmetricService;
use oat\taoEncryption\scripts\install\RegisterEncryptionSymmetricService;
use oat\taoEncryption\scripts\install\RegisterEncryptResultStorage;
use oat\taoEncryption\scripts\install\RegisterFileKeyProviderService;
use oat\taoEncryption\scripts\install\RegisterKeyPairProviderService;
use oat\taoEncryption\controller\EncryptionApi;
use oat\taoEncryption\scripts\install\RegisterSimpleKeyProviderService;
use oat\taoEncryption\scripts\install\RegisterTestSessionSyncMapper;

return array(
    'name' => 'taoEncryption',
    'label' => 'TAO encryption',
    'description' => 'TAO encryption',
    'license' => 'GPL-2.0',
    'version' => '3.2.1',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'tao' => '>=17.7.0',
        'generis' => '>=7.11.0',
        'taoResultServer' => '>=9.3.0',
        'taoSync' => '>=6.6.0',
        'taoProctoring' => '>=12.3.0',
        'taoTestCenter' => '>=4.1.0',
        'taoDeliveryRdf' => '>=8.3.0.1'
    ),
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#EncryptionRole',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#EncryptionRole', EncryptionApi::class),
    ),
    'install' => array(
        'rdf' => [
            __DIR__ . '/model/ontology/encryptionRole.rdf',
            __DIR__ . '/model/ontology/oauth.rdf',
        ],
        'php' => [
            RegisterKeyPairProviderService::class,
            RegisterEncryptionAsymmetricService::class,
            RegisterEncryptionSymmetricService::class,
            RegisterEncryptResultStorage::class,
            RegisterDecryptResultStorage::class,
            RegisterSimpleKeyProviderService::class,
            RegisterFileKeyProviderService::class,
            RegisterTestSessionSyncMapper::class,
        ]
    ),
    'uninstall' => array(
    ),
    'update' => \oat\taoEncryption\scripts\update\Updater::class,
    'routes' => array(
        '/taoEncryption' => 'oat\\taoEncryption\\controller'
    ),
);
