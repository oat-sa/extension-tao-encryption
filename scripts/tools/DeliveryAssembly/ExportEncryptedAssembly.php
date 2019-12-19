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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

namespace oat\taoEncryption\scripts\tools\DeliveryAssembly;

use Exception;
use tao_helpers_File;
use common_report_Report as Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoDeliveryRdf\model\export\AssemblyExporterService;
use oat\taoEncryption\Service\DeliveryAssembly\EncryptedAssemblyFilesReaderDecorator;
use oat\taoEncryption\Service\EncryptionServiceFactory;
use oat\taoEncryption\Service\EncryptionServiceInterface;
use oat\taoEncryption\Service\EncryptionSymmetricService;

class ExportEncryptedAssembly extends ScriptAction
{
    use OntologyAwareTrait;

    const OPTION_DELIVERY_URI = 'delivery-uri';

    const OPTION_OUTPUT = 'output';

    const OPTION_ENCRYPTION_ALGORITHM = 'encryption-algorithm';

    const OPTION_ENCRYPTION_KEY = 'encryption-key';

    /**
     * @var Report
     */
    private $report;

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Exports delivery assembly package with encrypted private files.';
    }

    /**
     * @return array
     */
    protected function provideOptions()
    {
        return [
            self::OPTION_DELIVERY_URI => [
                'prefix' => 'uri',
                'required' => true,
                'longPrefix' => self::OPTION_DELIVERY_URI,
                'description' => 'Delivery URI',
            ],
            self::OPTION_ENCRYPTION_ALGORITHM => [
                'prefix' => 'alg',
                'longPrefix' => self::OPTION_ENCRYPTION_ALGORITHM,
                'description' => 'Encryption algorithm',
                'defaultValue' => 'AES'
            ],
            self::OPTION_ENCRYPTION_KEY => [
                'prefix' => 'key',
                'required' => true,
                'longPrefix' => self::OPTION_ENCRYPTION_KEY,
                'description' => 'Encryption key',
            ],
            self::OPTION_OUTPUT => [
                'prefix' => 'out',
                'longPrefix' => self::OPTION_OUTPUT,
                'description' => 'Destination file path',
            ]
        ];
    }

    /**
     * @return Report
     */
    protected function run()
    {
        $this->report = Report::createInfo('Delivery export started');

        try {
            $deliveryUri = $this->getOption(self::OPTION_DELIVERY_URI);
            $this->report->add(Report::createInfo('Export delivery ' . $deliveryUri));
            $encryptionService = $this->getEncryptionService(
                $this->getOption(self::OPTION_ENCRYPTION_ALGORITHM),
                $this->getOption(self::OPTION_ENCRYPTION_KEY)
            );

            $assembler = $this->getAssemblyExporter($encryptionService);
            $delivery = $this->getResource($deliveryUri);

            $exportedAssemblyPath = $assembler->exportCompiledDelivery($delivery);

            if ($this->hasOption(self::OPTION_OUTPUT)) {
                tao_helpers_File::move($exportedAssemblyPath, $this->getOption(self::OPTION_OUTPUT));
                $exportedAssemblyPath = $this->getOption(self::OPTION_OUTPUT);
            }

            $this->report->add(Report::createSuccess(sprintf("Delivery assembly '%s' exported to %s", $delivery->getLabel(), $exportedAssemblyPath)));
        } catch (Exception $e) {
            $this->report->add(Report::createFailure("Export failed: " . $e->getMessage()));
        }

        return $this->report;
    }

    /**
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * @param EncryptionServiceInterface $encryptionService
     *
     * @return AssemblyExporterService
     */
    private function getAssemblyExporter(EncryptionServiceInterface $encryptionService)
    {
        $assemblerService = $this->getServiceLocator()->get(AssemblyExporterService::SERVICE_ID);
        $filesReader = $this->propagate($assemblerService->getOption(AssemblyExporterService::OPTION_ASSEMBLY_FILES_READER));
        $encryptedFilesReader = new EncryptedAssemblyFilesReaderDecorator($filesReader, $encryptionService);
        $assemblerService->setOption(AssemblyExporterService::OPTION_ASSEMBLY_FILES_READER, $encryptedFilesReader);

        return $assemblerService;
    }

    /**
     * @param $algorithmName
     * @param $key
     * @return EncryptionSymmetricService
     * @throws \Exception
     */
    private function getEncryptionService($algorithmName, $key)
    {
        $encryptionServiceFactory = new EncryptionServiceFactory();

        return $encryptionServiceFactory->createSymmetricService($algorithmName, $key);
    }
}
