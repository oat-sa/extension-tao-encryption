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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoEncryption\scripts\tools;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\mvc\error\ExceptionInterpreterService;
use oat\taoEncryption\Model\Exception\DecryptionExceptionInterpreter;
use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoEncryption\Service\Lti\LaunchData\Validator\LtiCustomerAppKeyValidator;
use common_report_Report as Report;
use oat\taoLti\models\classes\LaunchData\Validator\LtiValidatorService;

/**
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupLtiCustomerAppKeyValidator'
 */
class SetupLtiCustomerAppKeyValidator extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var LtiValidatorService $ltiValidatorService */
        $ltiValidatorService = $this->getServiceLocator()->get(LtiValidatorService::SERVICE_ID);
        $ltiValidatorService->setOption(LtiValidatorService::OPTION_LAUNCH_DATA_VALIDATOR, new LtiCustomerAppKeyValidator());
        $this->getServiceManager()->register(LtiValidatorService::SERVICE_ID, $ltiValidatorService);

        /** @var ExceptionInterpreterService $exceptionInterpreter */
        $exceptionInterpreter = $this->getServiceLocator()->get(ExceptionInterpreterService::SERVICE_ID);
        $exceptionInterpreter->setOption(ExceptionInterpreterService::OPTION_INTERPRETERS,
            array_merge($exceptionInterpreter->getOption(ExceptionInterpreterService::OPTION_INTERPRETERS), [
                DecryptionFailedException::class => DecryptionExceptionInterpreter::class
            ])
        );
        $this->getServiceManager()->register(ExceptionInterpreterService::SERVICE_ID, $exceptionInterpreter);

        return Report::createSuccess('LtiCustomerAppKeyValidator setup.');
    }
}