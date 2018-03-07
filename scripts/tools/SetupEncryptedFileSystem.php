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

use oat\oatbox\extension\script\ScriptAction;
use common_report_Report as Report;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoEncryption\Model\FileSystem\EncryptionFlyWrapper;

/**
 * Encrypted File System Setup
 *
 * This script aims at configuring a given file system on the platform in order to make
 * it encrypted. This script will NOT encrypt the existing data in the file system. In other words
 * it is suitable for fresh installs only.
 *
 * Example usage:
 *
 * sudo -u www-data php index.php "oat\taoEncryption\scripts\tools\SetupEncryptedFileSystem" --fileSystemId private -encryptionServiceId taoEncryption/symmetricEncryptionService
 *
 * Available arguments:
 *
 * Required Arguments:
 *   -f fileSystemId, --fileSystemId fileSystemId
 *     The File System ID as it appears in the TAO File System configuration
 *   -e encryptionServiceId, --encryptionServiceId encryptionServiceId
 *     The ID of the EncryptionService to be used for data encryption/decryption
 *
 * Optional Arguments:
 *   -h help, --help help
 *     Prints a help statement
 *
 * @package oat\taoEncryption\scripts\tools
 */
class SetupEncryptedFileSystem extends ScriptAction
{
    /**
     * Provide Description
     *
     * Provides the description of the script.
     *
     * @return string
     */
    protected function provideDescription()
    {
        return 'TAO Encryption - Encrypted File System Setup';
    }

    /**
     * Provide Options
     *
     * Provide the options of the script.
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'fileSystemId' => [
                'prefix' => 'f',
                'longPrefix' => 'fileSystemId',
                'required' => true,
                'description' => 'The File System ID as it appears in the TAO File System configuration'
            ],
            'encryptionServiceId' => [
                'prefix' => 'e',
                'longPrefix' => 'encryptionServiceId',
                'required' => true,
                'description' => 'The ID of the EncryptionService to be used for data encryption/decryption'
            ]
        ];
    }

    /**
     * Provide Usage
     *
     * Provides information about how/when should usage information displayed.
     *
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
     * Run Script
     *
     * Runs the main behaviours of the script.
     *
     * @return Report
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function run()
    {
        // Main report.
        $report = new Report(
            \common_report_Report::TYPE_INFO,
            "Script ended gracefully."
        );

        $fileSystemId = $this->getOption('fileSystemId');
        $encryptionServiceId = $this->getOption('encryptionServiceId');

        if (!$this->getServiceLocator()->has($encryptionServiceId)) {
            return new Report(
                Report::TYPE_ERROR,
                "No EncryptionService with ID '${encryptionServiceId}' available on the system."
            );
        }

        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        $fileSystemServiceOptions = $fileSystemService->getOption(FileSystemService::OPTION_ADAPTERS);

        if (isset($fileSystemServiceOptions[$fileSystemId])) {
            if ($fileSystemServiceOptions[$fileSystemId]['class'] === 'Local') {
                $fileSystemServiceOptions[$fileSystemId]['class'] = EncryptionFlyWrapper::class;
                $fileSystemServiceOptions[$fileSystemId]['options'] = [array_merge($fileSystemServiceOptions[$fileSystemId]['options'], ['encryptionServiceId' => $encryptionServiceId])];
                $fileSystemService->setOption(FileSystemService::OPTION_ADAPTERS, $fileSystemServiceOptions);

                $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $fileSystemService);

                $report->add(
                    new Report(Report::TYPE_SUCCESS, "Contents of File System '${fileSystemId}' will now be encrypted with EncryptionService '${encryptionServiceId}'.")
                );
            } elseif ($fileSystemServiceOptions[$fileSystemId]['class'] === EncryptionFlyWrapper::class) {
                $fileSystemServiceOptions[$fileSystemId]['options']['encryptionServiceId'] = $encryptionServiceId;
                $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $fileSystemService);

                $report->add(
                    new Report(Report::TYPE_SUCCESS, "Already encrypted File System '${fileSystemId}' will now be encrypted with EncryptionService '${encryptionServiceId}'.")
                );
            } else {
                return new Report(Report::TYPE_ERROR, "Only Local File Systems can be encrypted for the moment. File System '${fileSystemId}' is not.");
            }
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "No FileSystem with ID '${fileSystemId}' available on the system."
            );
        }

        return $report;
    }
}