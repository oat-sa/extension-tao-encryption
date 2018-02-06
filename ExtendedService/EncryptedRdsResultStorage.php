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

namespace oat\taoEncryption\ExtendedService;

use Doctrine\DBAL\Query\QueryBuilder;
use oat\taoEncryption\Encryption\EncryptionServiceInterface;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoOutcomeRds\model\RdsResultStorage;

class EncryptedRdsResultStorage extends RdsResultStorage implements EncryptResult
{
    const OPTION_ENCRYPTION_SERVICE = 'asymmetricEncryptionService';

    /**
     * @var bool
     */
    private $skipEncrypting = false;

    /**
     * @return EncryptionServiceInterface
     */
    public function getEncryptionService()
    {
        /** @var EncryptionServiceInterface $service */
        $service = $this->getServiceLocator()->get($this->getOption(static::OPTION_ENCRYPTION_SERVICE)) ;

        return $service;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function unserializeVariableValue($value)
    {
        return parent::unserializeVariableValue(($this->decryptVariable(base64_decode($value))));
    }

    /**
     * @param $value
     * @return string
     */
    protected function serializeVariableValue($value)
    {
        if ($this->skipEncrypting) {
            return parent::serializeVariableValue($value);
        }
        return base64_encode($this->encryptVariable(parent::serializeVariableValue($value))) ;
    }

    /**
     * @param string $data
     * @return string
     */
    public function encryptVariable($data)
    {
        return $this->getEncryptionService()->encrypt($data);
    }

    /**
     * @inheritdoc
     */
    public function decryptVariable($data)
    {
        return $this->getEncryptionService()->decrypt($data);
    }

    /**
     * @return bool|void
     */
    public function decryptAndSave()
    {
        $calls = $this->getAllCallIds();
        $this->skipEncrypting = true;

        if (!empty($calls)) {
            $resultSet = [];

            foreach ($calls as $callId) {
                try{
                    $variables = $this->getVariables($callId);
                }catch (DecryptionFailedException $exception){
                    //already decrypted skip
                    continue;
                }

                foreach ($variables as $variable) {
                    $result = array_pop($variable);
                    $deliveryResultIdentifier = $result->deliveryResultIdentifier;

                    if ($result->item === null){
                        $test = $result->test;
                        $callIdTest = $result->callIdTest;
                        $key = $deliveryResultIdentifier.'|@%@|' . $test . '|@%@|' . $callIdTest;

                        $resultSet[$deliveryResultIdentifier]['tests'][$key][] = $result->variable;
                    } else {
                        $test = $result->test;
                        $item = $result->item;
                        $callIdItem = $result->callIdItem;
                        $key = $deliveryResultIdentifier.'|@%@|' . $test . '|@%@|' . $callIdItem .'|@%@|'. $item;

                        $resultSet[$deliveryResultIdentifier]['items'][$key][] = $result->variable;
                    }
                }
            }

            foreach ($resultSet as $callId => $value) {
                $deleted = $this->deleteVariables($callId);
                if ($deleted) {
                    foreach ($value['tests'] as $key => $tests) {
                        list($deliveryResultIdentifier, $test, $callIdTest) = explode('|@%@|', $key);
                        $this->storeTestVariables($deliveryResultIdentifier, $test, $tests, $callIdTest);
                    }

                    foreach ($value['items'] as $key => $items) {
                        list($deliveryResultIdentifier, $test, $callIdItem, $item) = explode('|@%@|', $key);
                        $this->storeItemVariables($deliveryResultIdentifier, $test, $item, $items, $callIdItem);
                    }
                }
            }
        }
    }

    /**
     * @param $resultId
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    private function deleteVariables($resultId)
    {

        $qb = $this->getQueryBuilder()
            ->delete(static::VARIABLES_TABLENAME)
            ->where(static::VARIABLES_FK_COLUMN . ' = :id')
            ->setParameter('id', (string)$resultId);

        return $qb->execute();
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuilder()
    {
        /**@var \common_persistence_sql_pdo_mysql_Driver $driver */
        return $this->getPersistence()->getPlatform()->getQueryBuilder();
    }
}