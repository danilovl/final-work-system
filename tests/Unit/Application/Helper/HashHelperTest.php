<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Unit\Application\Helper;

use App\Application\Helper\HashHelper;
use PHPUnit\Framework\TestCase;

class HashHelperTest extends TestCase
{
    public function testGenerateResetPasswordHashedToken()
    {
        $hash = HashHelper::generateResetPasswordHashedToken('data', 'signingKey');

        $this->assertEquals('/XmpE5hILa08HC0IEq6VM7wFHBpcIflG3Mheayc0ADU=', $hash);
    }

    public function testGenerateDefaultHash()
    {
        $hash = HashHelper::generateDefaultHash();

        $this->assertEquals(40, strlen($hash));
    }

    public function testGenerateUserSalt()
    {
        $hash = HashHelper::generateUserSalt();

        $this->assertEquals(43, strlen($hash));
    }
}
