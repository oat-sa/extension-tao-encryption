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

namespace oat\taoEncryption\Service\Lti\LaunchData;

use common_persistence_SqlPersistence;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\service\ConfigurableService;

class EncryptedLtiLaunchDataStorage extends ConfigurableService
{
    const SERVICE_ID = 'taoEncryption/EncryptedLtiLaunchDataStorage';

    const OPTION_PERSISTENCE = 'persistence';

    const OPTION_ENCRYPTION_DATA = 'encryption_data';

    const PREFIX_KEY = 'lti_launch_data';

    const TABLE_NAME = 'lti_launch_data_sync';
    const COLUMN_USER_ID = 'user_id';
    const COLUMN_SERIALIZED = 'serialized';
    const COLUMN_CONSUMER = 'consumer';
    const COLUMN_IS_SYNC = 'is_sync';

    /** @var common_persistence_SqlPersistence */
    private $persistence;

    /**
     * @param $limit
     * @param $offset
     * @return array
     * @throws \Exception
     */
    public function getUsersToSync($limit = 20, $offset = 0)
    {
        $qb = $this->getQueryBuilder()
            ->select(static::COLUMN_USER_ID ,static::COLUMN_SERIALIZED, static::COLUMN_CONSUMER)
            ->from(static::TABLE_NAME)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->andWhere(self::COLUMN_IS_SYNC .' = :is_sync')
            ->setParameter('is_sync', 0);

        return $qb->execute()->fetchAll();
    }

    /**
     * @param $userIds
     * @return bool
     * @throws \Exception
     */
    public function setUsersAsSynced($userIds)
    {
        $qb = $this->getQueryBuilder()
            ->update(static::TABLE_NAME)
            ->set(static::COLUMN_IS_SYNC, ':is_sync')
            ->where(static::COLUMN_USER_ID . ' IN (:user_ids)')
            ->setParameter('user_ids', $userIds, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
            ->setParameter('is_sync', 1);

        return $qb->execute();
    }

    /**
     * @param $userId
     * @return bool|string
     * @throws \Exception
     */
    public function getEncrypted($userId)
    {
        $qb = $this->getQueryBuilder()
            ->select(static::COLUMN_SERIALIZED)
            ->from(static::TABLE_NAME)
            ->andWhere(static::COLUMN_USER_ID .' = :user_id')
            ->setParameter('user_id', $userId);

        return $qb->execute()->fetchColumn();
    }

    /**
     * @param EncryptedLtiLaunchData $launchData
     * @return bool
     * @throws \oat\taoLti\models\classes\LtiVariableMissingException
     */
    public function save(EncryptedLtiLaunchData $launchData)
    {
        $userId = $launchData->getUserID();
        $consumer = $launchData->getLtiConsumer()->getUri();

        $encrypted = $this->getEncryptLaunchDataService()->encrypt($launchData);
        $existedLtiData = $this->getEncrypted($userId);

        if ($existedLtiData !== false) {
            $qb = $this->getQueryBuilder()
                ->update(static::TABLE_NAME)
                ->set(static::COLUMN_SERIALIZED, ':serialized')
                ->set(static::COLUMN_IS_SYNC, ':is_sync')
                ->set(static::COLUMN_CONSUMER, ':consumer')
                ->where(self::COLUMN_USER_ID .' = :user_id')
                ->setParameter('user_id', (string) $userId)
                ->setParameter('serialized', $encrypted)
                ->setParameter('consumer', $consumer)
                ->setParameter('is_sync', 0);

            return $qb->execute();
        }

        if ($existedLtiData == false){

            $data = [
                static::COLUMN_USER_ID => $userId,
                static::COLUMN_SERIALIZED => $encrypted,
                static::COLUMN_CONSUMER => $consumer,
                static::COLUMN_IS_SYNC => 0,
            ];

            return $this->getPersistence()->insert(static::TABLE_NAME, $data);
        }

        return true;
    }

    /**
     * @param $encrypted
     * @param $appKey
     * @return EncryptedLtiLaunchData
     * @throws \Exception
     */
    public function getDecryptedLtiLaunchData($encrypted, $appKey)
    {
        return $this->getEncryptLaunchDataService()->decrypt($encrypted, $appKey);
    }

    /**
     * @throws \Exception
     */
    public function createTable()
    {
        $schemaManager = $this->getPersistence()->getDriver()->getSchemaManager();
        /** @var Schema $schema */
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        if (!$schema->hasTable(static::TABLE_NAME)){
            $tableLog = $schema->createTable(static::TABLE_NAME);
            $tableLog->addOption('engine', 'InnoDB');
            $tableLog->addColumn(static::COLUMN_USER_ID, 'string', ['notnull' => true, 'length' => 255]);
            $tableLog->addColumn(static::COLUMN_SERIALIZED, 'text', ['notnull' => true]);
            $tableLog->addColumn(static::COLUMN_CONSUMER, 'string', ['notnull' => true]);
            $tableLog->addColumn(static::COLUMN_IS_SYNC, 'string', ['notnull' => false, 'length' => 255]);
            $tableLog->setPrimaryKey([static::COLUMN_USER_ID]);

            $queries = $this->getPersistence()->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
            foreach ($queries as $query) {
                $this->getPersistence()->exec($query);
            }
        }
    }

    /**
     * @throws \Exception
     * @return common_persistence_SqlPersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)){
            $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
            $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);

            if (!$persistence instanceof common_persistence_SqlPersistence) {
                throw new \Exception('Only common_persistence_SqlPersistence supported');
            }

            $this->persistence = $persistence;
        }

        return $this->persistence;
    }

    /**
     * @return EncryptLaunchDataService
     */
    protected function getEncryptLaunchDataService()
    {
        return $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_DATA));
    }

    /**
     * @return QueryBuilder
     * @throws \Exception
     */
    private function getQueryBuilder()
    {
        /**@var \common_persistence_sql_pdo_mysql_Driver $driver */
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }
}