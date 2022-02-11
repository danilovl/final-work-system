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
            UserRoleConstant::ADMIN,
            UserRoleConstant::SUPERVISOR,
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
        $this->assertTrue(UserRoleHelper::hasRole($this->user, UserRoleConstant::ADMIN));
        $this->assertTrue(UserRoleHelper::hasRole($this->user, UserRoleConstant::SUPERVISOR));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::STUDENT));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::CONSULTANT));
        $this->assertFalse(UserRoleHelper::hasRole($this->user, UserRoleConstant::OPPONENT));
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
        $this->assertTrue(\App\Application\Helper\UserRoleHelper::isAuthorSupervisor($this->user));
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
