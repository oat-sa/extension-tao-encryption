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

use common_report_Report as Report;
use oat\oatbox\action\Action;
use oat\oatbox\action\ResolutionException;
use oat\taoEncryption\Service\KeyProvider\AsymmetricKeyPairProviderService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupAsymmetricKeys'
 */
class SetupAsymmetricKeys implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var array Available script modes
     */
    static public $options = ['generate'];

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var array list of given params
     */
    protected $params;

    /**
     * @param $params
     * @return Report
     * @throws \common_exception_Error
     */
    public function __invoke($params)
    {
        $this->params = $params;

        try {
            $this->process();

        } catch (\Exception $e) {
            $this->helpAction($e->getMessage());
        }

        return $this->report;
    }

    /**
     * @throws ResolutionException
     * @throws \Exception
     */
    private function process()
    {
        switch ($this->getOptionUsed()){
            case  'generate':
                 $this->generateKeys();
                break;
        }
    }

    /**
     * @throws \Exception
     */
    private function generateKeys()
    {
        /** @var AsymmetricKeyPairProviderService $service */
        $service = $this->getServiceLocator()->get(AsymmetricKeyPairProviderService::SERVICE_ID);
        $pairModel =  $service->getKeyPairModel();
        $pair = $pairModel->generate();

        $pairModel->savePrivateKey($pair->getPrivateKey());
        $pairModel->savePublicKey($pair->getPublicKey());

        $this->report = Report::createSuccess('Keys saved with success');
    }

    /**
     * @return bool
     */
    protected function getTypeOfKey()
    {
        $param = isset($this->params[1]) ? $this->params[1] : false;
        list($option, $value) = explode('=', $param);
        if ($option !== '--key') {
            throw new \Exception('Key parameter needs to be provided');
        }

        return $value;
    }

    /**
     * @throws ResolutionException
     * @return string
     */
    private function getOptionUsed()
    {
        $mode = isset($this->params[0]) ? $this->params[0] : false;

        if (!in_array($mode, self::$options)) {
            throw new ResolutionException('Wrong mode was specified');
        }
        return $mode;
    }

    /**
     * Set help report
     * @param string $message error message to be shown before help information
     * @throws \common_exception_Error
     */
    private function helpAction($message = null)
    {
        if ($message !== null) {
            $this->report = new Report(
                Report::TYPE_ERROR,
                $message . PHP_EOL
            );
        }

        $helpReport = new Report(
            Report::TYPE_INFO,
            "Usage: " . __CLASS__ . " <mode> [<args>]" . PHP_EOL . PHP_EOL
            . "Available modes:" . PHP_EOL
            . "  generate generate and save asymmetric keys" . PHP_EOL
        );

        if ($this->report) {
            $this->report->add($helpReport);
        } else {
            $this->report = $helpReport;
        }
    }
}