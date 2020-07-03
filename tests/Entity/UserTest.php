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

namespace App\Tests\Entity;

use App\Entity\User;
use DateTime;
use Generator;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @dataProvider gettersAndSettersProvider
     */
    public function testGettersAndSetters(
        $value,
        string $set,
        string $get
    ) {
        $entity = new class extends User {};
        $entity->$set($value);

        $this->assertEquals($value, $entity->$get());
    }

    public function gettersAndSettersProvider(): Generator
    {
        yield [1, 'setId', 'getId'];
        yield ['First name', 'setFirstname', 'getFirstname'];
        yield ['Last name', 'setLastname', 'getLastname'];
        yield [new DateTime('now'), 'setLastLogin', 'getLastLogin'];
        yield ['test@test.test', 'setEmail', 'getEmail'];
        yield [true, 'setEnabled', 'isEnabled'];
        yield ['asdakj@KLldjla2jd', 'setSalt', 'getSalt'];
        yield [null, 'setLocale', 'getLocale'];
    }
}
