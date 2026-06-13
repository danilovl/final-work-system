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
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkListService;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WorkListServiceTest extends TestCase
{
    private WorkListService $workListService;

    private User $user;

    protected function setUp(): void
    {
        $this->workListService = new WorkListService(new UserWorkService);
        $this->user = new User;

        $supervisorWorks = new ArrayCollection;
        $authorWorks = new ArrayCollection;

        $createUser = static function (): User {
            $user = new User;
            $user->setEnabled(true);

            return $user;
        };

        $work = new Work;
        $work->setAuthor($createUser());
        $work->setOpponent($createUser());
        $work->setSupervisor($createUser());
        $work->setConsultant($createUser());
        $status = new WorkStatus;
        $status->setId(WorkStatusConstant::ACTIVE->value);
        $work->setStatus($status);

        $supervisorWorks->add($work);
        $authorWorks->add($work);

        $work = new Work;
        $work->setAuthor($createUser());
        $work->setSupervisor($createUser());
        $status = new WorkStatus;
        $status->setId(WorkStatusConstant::ACTIVE->value);
        $work->setStatus($status);
        $supervisorWorks->add($work);

        $work = new Work;
        $work->setAuthor($createUser());
        $work->setSupervisor($createUser());
        $status = new WorkStatus;
        $status->setId(WorkStatusConstant::ARCHIVE->value);
        $work->setStatus($status);
        $supervisorWorks->add($work);

        $this->user->setSupervisorWorks($supervisorWorks);
        $this->user->setAuthorWorks($authorWorks);
    }

    #[DataProvider('provideGetWorkListCases')]
    public function testGetWorkList(string $type, int $expectedCount): void
    {
        $result = $this->workListService->getWorkList($this->user, $type);

        $this->assertCount($expectedCount, $result);
    }

    #[DataProvider('provideGetUserAuthorsCases')]
    public function testGetUserAuthors(string $type, int $expectedCount): void
    {
        $result = $this->workListService->getUserAuthors($this->user, $type);

        $this->assertCount($expectedCount, $result);
    }

    #[DataProvider('provideGetUserSupervisorsCases')]
    public function testGetUserSupervisors(string $type, int $expectedCount): void
    {
        $result = $this->workListService->getUserSupervisors($this->user, $type);

        $this->assertCount($expectedCount, $result);
    }

    #[DataProvider('provideGetUserOpponentsCases')]
    public function testGetUserOpponents(string $type, int $expectedCount): void
    {
        $result = $this->workListService->getUserOpponents($this->user, $type);

        $this->assertCount($expectedCount, $result);
    }

    #[DataProvider('provideGetUserConsultantsCases')]
    public function testGetUserConsultants(string $type, int $expectedCount): void
    {
        $result = $this->workListService->getUserConsultants($this->user, $type);

        $this->assertCount($expectedCount, $result);
    }

    public static function provideGetWorkListCases(): Generator
    {
        yield [WorkUserTypeConstant::SUPERVISOR->value, 3];
        yield [WorkUserTypeConstant::AUTHOR->value, 1];
        yield [WorkUserTypeConstant::OPPONENT->value, 0];
        yield [WorkUserTypeConstant::CONSULTANT->value, 0];
    }

    public static function provideGetUserAuthorsCases(): Generator
    {
        yield [WorkUserTypeConstant::SUPERVISOR->value, 2];
        yield [WorkUserTypeConstant::AUTHOR->value, 0];
        yield [WorkUserTypeConstant::OPPONENT->value, 0];
        yield [WorkUserTypeConstant::CONSULTANT->value, 0];
    }

    public static function provideGetUserSupervisorsCases(): Generator
    {
        yield [WorkUserTypeConstant::SUPERVISOR->value, 0];
        yield [WorkUserTypeConstant::AUTHOR->value, 1];
        yield [WorkUserTypeConstant::OPPONENT->value, 0];
        yield [WorkUserTypeConstant::CONSULTANT->value, 0];
    }

    public static function provideGetUserOpponentsCases(): Generator
    {
        yield [WorkUserTypeConstant::SUPERVISOR->value, 1];
        yield [WorkUserTypeConstant::AUTHOR->value, 1];
        yield [WorkUserTypeConstant::OPPONENT->value, 0];
        yield [WorkUserTypeConstant::CONSULTANT->value, 0];
    }

    public static function provideGetUserConsultantsCases(): Generator
    {
        yield [WorkUserTypeConstant::SUPERVISOR->value, 1];
        yield [WorkUserTypeConstant::AUTHOR->value, 1];
        yield [WorkUserTypeConstant::OPPONENT->value, 0];
        yield [WorkUserTypeConstant::CONSULTANT->value, 0];
    }
}
