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
namespace oat\taoEncryption\Model\Model;

use oat\taoEncryption\Model\Exception\DecryptionFailedException;
use oat\taoEncryption\Model\Key;
use oat\taoEncryption\Model\Symmetric\Symmetric;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\RC4;
use PHPUnit\Framework\TestCase;

class SymmetricTest extends TestCase
{
    public function testSuccessFlow()
    {
        $sym = new Symmetric(new RC4());

        $myKey = new Key('secret key');

        $encrypted = $sym->encrypt($myKey, 'secret banana');

        $this->assertIsString($encrypted);

        $this->assertSame('secret banana',  $sym->decrypt($myKey, $encrypted));
    }

    public function testUnSuccessFlow()
    {
        $this->expectException(DecryptionFailedException::class);

        $sym = new Symmetric(new AES());

        $myKey = new Key('secret key');

        $encrypted = $sym->encrypt($myKey, 'secret banana');

        $this->assertIsString($encrypted);

        $sym->decrypt(new Key('anotherkey'), $encrypted);
    }


}
