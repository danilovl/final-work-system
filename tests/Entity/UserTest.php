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

use App\Entity\{
    User,
    Media
};
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
        yield [new DateTime, 'setLastLogin', 'getLastLogin'];
        yield ['test@test.test', 'setEmail', 'getEmail'];
        yield [true, 'setEnabled', 'isEnabled'];
        yield ['test@test.com', 'setSalt', 'getSalt'];
        yield [null, 'setLocale', 'getLocale'];
        yield ['username', 'setUsername', 'getUserIdentifier'];
        yield [null, 'setSalt', 'getSalt'];
        yield ['en', 'setLocale', 'getLocale'];
        yield [null, 'setLocale', 'getLocale'];
        yield ['888-888-888', 'setPhone', 'getPhone'];
        yield [null, 'setPhone', 'getPhone'];
        yield ['token', 'setToken', 'getToken'];
        yield [null, 'setToken', 'getToken'];
        yield ['username-canonical', 'setUsernameCanonical', 'getUsernameCanonical'];
        yield [null, 'setUsernameCanonical', 'getUsernameCanonical'];
        yield [new ArrayCollection, 'setAuthorWorks', 'getAuthorWorks'];
        yield [new ArrayCollection, 'setEventsOwner', 'getEventsOwner'];
        yield [new ArrayCollection, 'setComments', 'getComments'];
        yield [new ArrayCollection, 'setGroups', 'getGroups'];
        yield [new class extends Media {}, 'setProfileImage', 'getProfileImage'];
    }
}
