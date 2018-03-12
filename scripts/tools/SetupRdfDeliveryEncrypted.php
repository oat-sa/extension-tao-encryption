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
use oat\oatbox\extension\InstallAction;
use oat\taoEncryption\Rdf\EncryptedDeliveryRdf;
use oat\taoEncryption\Service\Sync\EncryptDeliverySynchronizerService;
use oat\taoEncryption\Service\Sync\EncryptRdfDeliverySynchronizer;
use oat\taoSync\model\synchronizer\AbstractResourceSynchronizer;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\taoDelivery\model\fields\DeliveryFieldsService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\taoProctoring\model\ProctorService;
use oat\taoResultServer\models\classes\implementation\OntologyService;
use oat\taoSync\model\SyncService;

/**
 * Class SetupAsymmetricKeys
 * @package oat\taoEncryption\tools
 *
 * sudo -u www-data php index.php 'oat\taoEncryption\scripts\tools\SetupDeliveryEncrypted'
 */
class SetupRdfDeliveryEncrypted extends InstallAction
{
    /**
     * @param $params
     * @return Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);

        if (!$extensionManager->isInstalled('taoSync')) {
            return Report::createSuccess('Cannot setup sync, to taoSync extension installed');
        }

        $rdfDeliverySync = new EncryptRdfDeliverySynchronizer([
            AbstractResourceSynchronizer::OPTIONS_FIELDS => array(
                OntologyRdf::RDF_TYPE,
                OntologyRdfs::RDFS_LABEL,
                OntologyService::PROPERTY_RESULT_SERVER,
                DeliveryContainerService::PROPERTY_MAX_EXEC,
                DeliveryAssemblyService::PROPERTY_DELIVERY_DISPLAY_ORDER_PROP,
                DeliveryContainerService::PROPERTY_ACCESS_SETTINGS,
                DeliveryAssemblyService::PROPERTY_END,
                DeliveryFieldsService::PROPERTY_CUSTOM_LABEL,
                ProctorService::ACCESSIBLE_PROCTOR,
                DeliveryAssemblyService::PROPERTY_START,
                EncryptedDeliveryRdf::PROPERTY_APPLICATION_KEY,
            )
        ]);

        $this->registerService(EncryptRdfDeliverySynchronizer::SERVICE_ID, $rdfDeliverySync);

        /** @var SyncService $syncService */
        $syncService = $this->getServiceLocator()->get(SyncService::SERVICE_ID);
        $synchronizers = $syncService->getOption(SyncService::OPTION_SYNCHRONIZERS);

        $syncService->setOptions(array_merge(
            $syncService->getOptions(),
            [
                SyncService::OPTION_SYNCHRONIZERS => array_merge($synchronizers,[
                    'delivery' => EncryptRdfDeliverySynchronizer::SERVICE_ID,
                ])
            ]
        ));

        $this->registerService(SyncService::SERVICE_ID, $syncService);

        $encryptDeliverySinchronize = new EncryptDeliverySynchronizerService();

        $this->registerService(EncryptDeliverySynchronizerService::SERVICE_ID, $encryptDeliverySinchronize);

        return Report::createSuccess('EncryptRdfDeliverySynchronizer and EncryptDeliverySynchronizerService setup');
    }

}