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

namespace oat\taoEncryption\Service\Mapper;

use common_persistence_KeyValuePersistence;
use oat\oatbox\service\ConfigurableService;

class TestSessionSyncMapper extends ConfigurableService
{
    const SERVICE_ID = 'taoEncryption/TestSessionMapper';

    const OPTION_PERSISTENCE = 'persistence';

    const PREFIX_MAPPER = 'mapTestSessionSynced_';

    /** @var common_persistence_KeyValuePersistence */
    private $persistence;

    /**
     * @throws \Exception
     * @return common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)){
            $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
            $persistence = $this->getServiceLocator()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);

            if (!$persistence instanceof common_persistence_KeyValuePersistence) {
                throw new \Exception('Only common_persistence_KeyValuePersistence supported');
            }

            $this->persistence = $persistence;
        }

        return $this->persistence;
    }

    /**
     * @param string $resultId
     * @param string $value
     * @return bool
     * @throws \common_Exception
     * @throws \Exception
     */
    public function set($resultId, $value)
    {
        return $this->getPersistence()->set(self::PREFIX_MAPPER . $resultId, $value);
    }

    /**
     * @param string $resultId
     * @return bool|int|null|string
     * @throws \Exception
     */
    public function get($resultId)
    {
        return $this->getPersistence()->get(self::PREFIX_MAPPER . $resultId);
    }

    /**
     * @param $resultId
     * @return bool
     * @throws \Exception
     */
    public function delete($resultId)
    {
        return $this->getPersistence()->del(self::PREFIX_MAPPER . $resultId);
    }
}