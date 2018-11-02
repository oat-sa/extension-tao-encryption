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

namespace oat\taoEncryption\Model\Exception;

use oat\tao\model\mvc\error\ExceptionInterpretor;
use oat\tao\model\mvc\error\MainResponse;
use oat\taoLti\models\classes\ExceptionInterpreter;
use oat\taoLti\models\classes\LtiException;

class DecryptionExceptionInterpreter extends ExceptionInterpreter
{
    /**
     * set exception to interpet
     * @param \Exception $exception
     * @return ExceptionInterpretor
     */
    public function setException(\Exception $exception)
    {
        $isAjax = \tao_helpers_Request::isAjax();

        if (!$isAjax) {
            $ltiException = new LtiException($exception->getMessage());
            parent::setException($ltiException);
            return $this;
        }

        parent::setException($exception);
        return $this;
    }

    /**
     * @return \oat\tao\model\mvc\error\ResponseInterface
     */
    public function getResponse()
    {
        $isAjax = \tao_helpers_Request::isAjax();

        if (!$isAjax) {
            return parent::getResponse();
        }
        $class = MainResponse::class;
        /*@var $response ResponseAbstract */
        $response = new $class;
        $response->setServiceLocator($this->getServiceLocator())
            ->setException($this->exception)
            ->setHttpCode($this->returnHttpCode)
            ->trace($this->trace);
        return $response;
    }
}