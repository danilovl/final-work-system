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

namespace App\Tests\Unit\Domain\Work\Service;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkService;
use DateTime;
use PHPUnit\Framework\TestCase;

class WorkServiceTest extends TestCase
{
    private readonly Work $work;
    private readonly WorkService $workService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workService = new WorkService;
        $this->work = new Work;

        $user = new User;
        $user->setId(1);
        $this->work->setSupervisor($user);

        $user = new User;
        $user->setId(2);
        $this->work->setAuthor($user);

        $user = new User;
        $user->setId(3);
        $this->work->setOpponent($user);

        $user = new User;
        $user->setId(4);
        $this->work->setConsultant($user);
    }

    public function testIsParticipantSuccess(): void
    {
        $user = new User;
        $user->setId(random_int(1, 4));

        $result = $this->workService->isParticipant($this->work, $user);

        $this->assertTrue($result);
    }

    public function testIsParticipantFailed(): void
    {
        $user = new User;
        $user->setId(5);

        $result = $this->workService->isParticipant($this->work, $user);

        $this->assertFalse($result);
    }

    public function testGetAllUsers(): void
    {
        $users = $this->workService->getAllUsers($this->work);

        $this->assertCount(4, $users);
    }

    public function testGetUsersAuthor(): void
    {
        $users = $this->workService->getUsers(
            work: $this->work,
            author: true
        );

        $this->assertCount(1, $users);
    }

    public function testGetAllUsersSupervisor(): void
    {
        $users = $this->workService->getUsers(
            work: $this->work,
            supervisor: true
        );

        $this->assertCount(1, $users);
    }

    public function testGetAllUsersOpponent(): void
    {
        $users = $this->workService->getUsers(
            work: $this->work,
            opponent: true
        );

        $this->assertCount(1, $users);
    }

    public function testGetAllUsersConsultant(): void
    {
        $users = $this->workService->getUsers(
            work: $this->work,
            consultant: true
        );

        $this->assertCount(1, $users);
    }

    public function testGetDeadlineDays(): void
    {
        $this->work->setDeadline(new DateTime('2023-07-20'));
        $deadlineDays = $this->workService->getDeadlineDays($this->work);

        $this->assertEquals(4, $deadlineDays);
    }

    public function testGetDeadlineProgramDays(): void
    {
        $this->work->setDeadlineProgram(new DateTime('2023-07-10'));
        $deadlineProgramDays = $this->workService->getDeadlineProgramDays($this->work);

        $this->assertEquals(-5, $deadlineProgramDays);
    }
}
