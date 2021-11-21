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

namespace App\Tests\Helper;

use App\Constant\UserRoleConstant;
use App\Model\User\Entity\User;
use App\Helper\UserRoleHelper;
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
        $this->assertEquals(UserRoleHelper::isAuthorSupervisorOpponent($this->user), true);
    }

    public function testIsAuthorSupervisorOpponent(): void
    {
        $this->assertEquals(UserRoleHelper::isAuthorSupervisorOpponent($this->user), true);
    }

    public function testHasRole(): void
    {
        $this->assertEquals(UserRoleHelper::hasRole($this->user, UserRoleConstant::ADMIN), true);
        $this->assertEquals(UserRoleHelper::hasRole($this->user, UserRoleConstant::SUPERVISOR), true);
        $this->assertEquals(UserRoleHelper::hasRole($this->user, UserRoleConstant::STUDENT), false);
        $this->assertEquals(UserRoleHelper::hasRole($this->user, UserRoleConstant::CONSULTANT), false);
        $this->assertEquals(UserRoleHelper::hasRole($this->user, UserRoleConstant::OPPONENT), false);
    }

    public function testIsAuthor(): void
    {
        $this->assertEquals(UserRoleHelper::isAuthor($this->user), false);
    }

    public function testIsOpponent(): void
    {
        $this->assertEquals(UserRoleHelper::isOpponent($this->user), false);
    }

    public function testIsAuthorSupervisor(): void
    {
        $this->assertEquals(UserRoleHelper::isAuthorSupervisor($this->user), true);
    }

    public function testIsAdmin(): void
    {
        $this->assertEquals(UserRoleHelper::isAdmin($this->user), true);
    }

    public function testIsSupervisor(): void
    {
        $this->assertEquals(UserRoleHelper::isSupervisor($this->user), true);
    }

    public function testIsConsultant(): void
    {
        $this->assertEquals(UserRoleHelper::isConsultant($this->user), false);
    }
}
