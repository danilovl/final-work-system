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

use App\Entity\{
    User,
    Work
};
use App\Helper\WorkRoleHelper;
use PHPUnit\Framework\TestCase;

class WorkRoleHelperTest extends TestCase
{
    private Work $work;
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User;
        $this->user->setId(1);

        $this->work = new Work;
        $this->work->setSupervisor($this->user);
        $this->work->setAuthor($this->user);
        $this->work->setOpponent($this->user);
        $this->work->setConsultant($this->user);
    }

    public function testIsAuthorSupervisorOpponent(): void
    {
        $this->assertEquals(WorkRoleHelper::isAuthorSupervisorOpponent($this->work, $this->user), true);
    }

    public function testIsAuthorSupervisor(): void
    {
        $this->assertEquals(WorkRoleHelper::isAuthorSupervisor($this->work, $this->user), true);
    }

    public function testIsSupervisor(): void
    {
        $this->assertEquals(WorkRoleHelper::isSupervisor($this->work, $this->user), true);
    }

    public function testIsConsultant(): void
    {
        $this->assertEquals(WorkRoleHelper::isConsultant($this->work, $this->user), true);
    }

    public function testIsAuthor(): void
    {
        $this->assertEquals(WorkRoleHelper::isAuthor($this->work, $this->user), true);
    }

    public function testIsOpponent(): void
    {
        $this->assertEquals(WorkRoleHelper::isOpponent($this->work, $this->user), true);
    }
}
