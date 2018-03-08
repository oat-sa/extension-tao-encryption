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

namespace oat\taoEncryption\Service\KeyProvider;

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use oat\oatbox\service\ConfigurableService;
use oat\taoEncryption\controller\EncryptionApi;
use oat\taoPublishing\model\publishing\PublishingService;
use oat\taoSync\scripts\tool\synchronisation\SynchronizeData;

class KeyProviderClient extends ConfigurableService
{
    const SERVICE_ID = 'taoEncryption/KeyProviderClient';

    /**
     * Get a remote public key checksum
     *
     * @return \Psr\Http\Message\StreamInterface
     * @throws \common_Exception
     */
    public function getRemotePublicKeyChecksum()
    {
        $url = '/taoEncryption/EncryptionApi/getPublicKeyChecksum';
        $method = 'GET';

        $response = $this->call($url, $method);
        if ($response->getStatusCode() != 200) {
            throw new \common_Exception('An error has occurred during calling remote server with message : ' . $response->getBody()->getContents());
        }

        return $response->getBody();
    }

    /**
     * Send the encrypted public key
     *
     * @param $publicKey
     * @return \Psr\Http\Message\StreamInterface
     * @throws \common_Exception
     */
    public function sendPublicKey($publicKey)
    {
        $url = '/taoEncryption/EncryptionApi/savePublicKey?' . http_build_query([EncryptionApi::PARAM_PUBLIC_KEY => $publicKey]);
        $method = 'POST';

        $response = $this->call($url, $method);
        if ($response->getStatusCode() != 200) {
            throw new \common_Exception('An error has occurred during calling remote server with message : ' . $response->getBody()->getContents());
        }
        return $response->getBody();
    }

    /**
     * Process an http call to a remote environment
     *
     * @param $url
     * @param string $method
     * @param null $body
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \common_Exception
     */
    protected function call($url, $method = 'GET', $body = null)
    {
        $request = new Request($method, $url);
        if (!is_null($body)) {
            if (is_array($body)) {
                $body = stream_for(http_build_query($body));
            } elseif (is_string($body)) {
                $body = stream_for($body);
            }
            $request = $request->withBody($body);
        }

        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withHeader('Content-type', 'application/json');

        try {
            return $this->getServiceLocator()->get(PublishingService::SERVICE_ID)->callEnvironment(SynchronizeData::class, $request);
        } catch (\Exception $e) {
            throw new \common_Exception($e->getMessage(), 0, $e);
        }
    }
}