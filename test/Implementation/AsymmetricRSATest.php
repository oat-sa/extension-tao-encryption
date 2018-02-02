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
namespace oat\Encryption\Test\Implementation;

use oat\Encryption\Implementation\AsymmetricRSA;
use oat\Encryption\Model\PrivateKey;
use oat\Encryption\Model\PublicKey;
use PHPUnit\Framework\TestCase;

class AsymmetricRSATest extends TestCase
{
    private $publicKey = <<<'EOD'
           -----BEGIN PUBLIC KEY-----
            MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDB1ubesM1IZo2PZzuYQGTjzoYB
    BGbpIlDmFPX84/C+MLlxfndnlQaoMVYzQ0fewJHtyqmU+b3PtGmGN22Yn5C3eOni
    B6LGFb+Y8sysG9BxHMJdFpXfakpLkczfHI26sqF3zrNBfvgUDS3CB7MVNlC/6qjT
    jsFd1/BUbfGxpjZb7QIDAQAB
            -----END PUBLIC KEY-----
EOD;

    private $privateKey = <<<'EOD'
    -----BEGIN RSA PRIVATE KEY-----
    MIICXAIBAAKBgQDB1ubesM1IZo2PZzuYQGTjzoYBBGbpIlDmFPX84/C+MLlxfndn
    lQaoMVYzQ0fewJHtyqmU+b3PtGmGN22Yn5C3eOniB6LGFb+Y8sysG9BxHMJdFpXf
    akpLkczfHI26sqF3zrNBfvgUDS3CB7MVNlC/6qjTjsFd1/BUbfGxpjZb7QIDAQAB
    AoGAWJMVvi62L15FU4ENt129fGdzzmUvjVqT8v8jBKM38ACdzKHIeDmd6B9bT2Nw
    JPaD+FACO8P/GzlKev07BGHto0zYGXOefHCLQHZe3/dkVyu18x0PHhQmcENOb+bZ
    d4oZjVJDP0T6aZDMuk77DJSPKz423qsD3BB7PaiOD9qFzwECQQDwtW11NpHtv54G
    o4r7aasDL9PdKM6pf7oRNv/USg+CNyyd1ApwwSnYlmSlbA83+jEP4Tk+1bItNCPL
    D2c98WXBAkEAzidBCS2WAFrbyKXHoXaO16VV5kGfAVybNypdg7uOpBvkdReWYsCx
    o3zUWt8WNGaLmCkolbScAweAIUIDNf+5LQJAbm2tY6K/W+UWqFELB8A4dmPQvJtm
    BBjW0eL7hvbbGpAZZebLS2MywWxti/6BFNsw+uoGiy8aaOaMrTHJ2X8PgQJBAJyH
    P6FZZJi2ZETwYyic3Y6tchCX6MRe7VewqjqY0ZCXwRqLI1uuFfMdmBu7YZ+98OZC
    8hbhgMfoDQizl76Lga0CQA9N2ZjarcGP66o1PHoZWQTa98nHCWUQ1scoRwdaFPT/
    vDgn4/3jb2r424J5ik/ghJWRBlMd+f6ap8BR7ORhE2M=
    -----END RSA PRIVATE KEY-----
EOD;

    public function testFlow()
    {
        $rsa = new AsymmetricRSA();

        $encrypted = $rsa->encrypt(new PublicKey($this->publicKey), 'secret banana');

        $this->assertInternalType('string', $encrypted);

        $decrypted = $rsa->decrypt(new PrivateKey($this->privateKey), $encrypted);

        $this->assertSame('secret banana', $decrypted);
    }
}
