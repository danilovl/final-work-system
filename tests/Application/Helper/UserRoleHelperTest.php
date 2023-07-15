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

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use App\Domain\User\Helper\UserRoleHelper;
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
        $this->assertTrue(\App\Domain\User\Helper\UserRoleHelper::isAuthorSupervisorOpponent($this->user));
    }

    public function testIsAuthorSupervisorOpponent(): void
    {
        $this->assertTrue(\App\Domain\User\Helper\UserRoleHelper::isAuthorSupervisorOpponent($this->user));
    }

    public function testHasRole(): void
    {
        $this->assertTrue(UserRoleHelper::hasRole($this->user, UserRoleConstant::ADMIN->value));
        $this->assertTrue(\App\Domain\User\Helper\UserRoleHelper::hasRole($this->user, UserRoleConstant::SUPERVISOR->value));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::STUDENT->value));
        $this->assertFalse(\App\Domain\User\Helper\UserRoleHelper::hasRole($this->user, UserRoleConstant::CONSULTANT->value));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::OPPONENT->value));
    }

    public function testIsAuthor(): void
    {
        $this->assertFalse(\App\Domain\User\Helper\UserRoleHelper::isAuthor($this->user));
    }

    public function testIsOpponent(): void
    {
        $this->assertFalse(\App\Domain\User\Helper\UserRoleHelper::isOpponent($this->user));
    }

    public function testIsAuthorSupervisor(): void
    {
        $this->assertTrue(\App\Domain\User\Helper\UserRoleHelper::isAuthorSupervisor($this->user));
    }

    public function testIsAdmin(): void
    {
        $this->assertTrue(\App\Domain\User\Helper\UserRoleHelper::isAdmin($this->user));
    }

    public function testIsSupervisor(): void
    {
        $this->assertTrue(\App\Domain\User\Helper\UserRoleHelper::isSupervisor($this->user));
    }

    public function testIsConsultant(): void
    {
        $this->assertFalse(\App\Domain\User\Helper\UserRoleHelper::isConsultant($this->user));
    }
}
