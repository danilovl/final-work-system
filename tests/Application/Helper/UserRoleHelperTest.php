<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Application\Helper;

use App\Application\Constant\UserRoleConstant;
use App\Application\Helper\UserRoleHelper;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class UserRoleHelperTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User;
        $this->user->setRoles([
            UserRoleConstant::ADMIN->value,
            UserRoleConstant::SUPERVISOR->value,
        ]);
    }

    public function testIsAuthorSupervisorOpponentConsultant(): void
    {
        $this->assertTrue(UserRoleHelper::isAuthorSupervisorOpponent($this->user));
    }

    public function testIsAuthorSupervisorOpponent(): void
    {
        $this->assertTrue(UserRoleHelper::isAuthorSupervisorOpponent($this->user));
    }

    public function testHasRole(): void
    {
        $this->assertTrue(UserRoleHelper::hasRole($this->user, UserRoleConstant::ADMIN->value));
        $this->assertTrue(UserRoleHelper::hasRole($this->user, UserRoleConstant::SUPERVISOR->value));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::STUDENT->value));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::CONSULTANT->value));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::OPPONENT->value));
    }

    public function testIsAuthor(): void
    {
        $this->assertFalse(UserRoleHelper::isAuthor($this->user));
    }

    public function testIsOpponent(): void
    {
        $this->assertFalse(UserRoleHelper::isOpponent($this->user));
    }

    public function testIsAuthorSupervisor(): void
    {
        $this->assertTrue(UserRoleHelper::isAuthorSupervisor($this->user));
    }

    public function testIsAdmin(): void
    {
        $this->assertTrue(UserRoleHelper::isAdmin($this->user));
    }

    public function testIsSupervisor(): void
    {
        $this->assertTrue(UserRoleHelper::isSupervisor($this->user));
    }

    public function testIsConsultant(): void
    {
        $this->assertFalse(UserRoleHelper::isConsultant($this->user));
    }
}
