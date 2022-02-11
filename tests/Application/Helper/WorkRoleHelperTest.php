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

use App\Application\Helper\WorkRoleHelper;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
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
        $this->assertTrue(\App\Application\Helper\WorkRoleHelper::isAuthorSupervisorOpponent($this->work, $this->user));
    }

    public function testIsAuthorSupervisor(): void
    {
        $this->assertTrue(\App\Application\Helper\WorkRoleHelper::isAuthorSupervisor($this->work, $this->user));
    }

    public function testIsSupervisor(): void
    {
        $this->assertTrue(WorkRoleHelper::isSupervisor($this->work, $this->user));
    }

    public function testIsConsultant(): void
    {
        $this->assertTrue(\App\Application\Helper\WorkRoleHelper::isConsultant($this->work, $this->user));
    }

    public function testIsAuthor(): void
    {
        $this->assertTrue(\App\Application\Helper\WorkRoleHelper::isAuthor($this->work, $this->user));
    }

    public function testIsOpponent(): void
    {
        $this->assertTrue(\App\Application\Helper\WorkRoleHelper::isOpponent($this->work, $this->user));
    }
}
