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

namespace App\Tests\Unit\Domain\User\Traits\Entity;

use App\Domain\User\Entity\User;
use App\Domain\User\Traits\Entity\IsOwnerTrait;
use PHPUnit\Framework\TestCase;

class IsOwnerTraitTest extends TestCase
{
    public function testIsOwner(): void
    {
        $class = new class() {
            use IsOwnerTrait;

            public function getOwner(): User
            {
                $user = new User;
                $user->setId(1);

                return $user;
            }
        };

        $user = new User;
        $user->setId(2);

        $this->assertFalse($class->isOwner($user));

        $user = new User;
        $user->setId(1);

        $this->assertTrue($class->isOwner($user));
    }
}
