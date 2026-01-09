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

namespace App\Tests\Unit\Domain\User\Helper;

use App\Domain\User\Entity\User;
use App\Domain\User\Helper\SortFunctionHelper;
use PHPUnit\Framework\TestCase;

class SortFunctionHelperTest extends TestCase
{
    public function testUsortCzechUserArray(): void
    {
        $user1 = new User;
        $user1->setFirstname('žbaňka');
        $user1->setLastname('');

        $user2 = new User;
        $user2->setFirstname('ářam');
        $user2->setLastname('');

        $user3 = new User;
        $user3->setFirstname('čary');
        $user3->setLastname('');

        $users = [$user1, $user2, $user3];
        SortFunctionHelper::usortCzechUserArray($users);

        $this->assertEquals($user2, $users[0]);
        $this->assertEquals($user3, $users[1]);
        $this->assertEquals($user1, $users[2]);
    }
}
