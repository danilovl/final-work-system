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

namespace App\Tests\Unit\Domain\WorkCategory\Validator\Constraints;

use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;
use App\Domain\WorkCategory\Validator\Constraints\{
    WorkCategoryOwner,
    WorkCategoryOwnerValidator
};
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[AllowMockObjectsWithoutExpectations]
class WorkCategoryOwnerValidatorTest extends ConstraintValidatorTestCase
{
    private MockObject&WorkCategoryFacade $workCategoryFacade;

    private MockObject&UserService $userService;

    private User $user;

    protected function setUp(): void
    {
        $this->workCategoryFacade = $this->createMock(WorkCategoryFacade::class);
        $this->userService = $this->createMock(UserService::class);
        $this->user = new User;
        $this->user->setId(1);

        parent::setUp();
    }

    protected function createValidator(): WorkCategoryOwnerValidator
    {
        return new WorkCategoryOwnerValidator(
            $this->workCategoryFacade,
            $this->userService
        );
    }

    private function createWorkCategory(int $id): WorkCategory
    {
        $workCategory = new WorkCategory;
        $workCategory->setId($id);

        return $workCategory;
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new WorkCategoryOwner);

        $this->assertNoViolation();
    }

    public function testEmptyArrayIsValid(): void
    {
        $this->validator->validate([], new WorkCategoryOwner);

        $this->assertNoViolation();
    }

    public function testSingleIdValid(): void
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->workCategoryFacade->expects($this->once())
            ->method('findByOwnerAndIds')
            ->with($this->user, [38])
            ->willReturn([$this->createWorkCategory(38)]);

        $this->validator->validate(38, new WorkCategoryOwner);

        $this->assertNoViolation();
    }

    public function testArrayIdsValid(): void
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->workCategoryFacade->expects($this->once())
            ->method('findByOwnerAndIds')
            ->with($this->user, [38, 39])
            ->willReturn([
                $this->createWorkCategory(38),
                $this->createWorkCategory(39)
            ]);

        $this->validator->validate([38, 39], new WorkCategoryOwner);

        $this->assertNoViolation();
    }

    public function testArrayIdsOrderIndependentValid(): void
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->workCategoryFacade->expects($this->once())
            ->method('findByOwnerAndIds')
            ->with($this->user, [39, 38])
            ->willReturn([
                $this->createWorkCategory(38),
                $this->createWorkCategory(39)
            ]);

        $this->validator->validate([39, 38], new WorkCategoryOwner);

        $this->assertNoViolation();
    }

    public function testSingleIdNotOwned(): void
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->workCategoryFacade->expects($this->once())
            ->method('findByOwnerAndIds')
            ->with($this->user, [38])
            ->willReturn([]);

        $constraint = new WorkCategoryOwner;
        $this->validator->validate(38, $constraint);

        $this->buildViolation($constraint->message)->assertRaised();
    }

    public function testPartialMatchNotValid(): void
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->workCategoryFacade->expects($this->once())
            ->method('findByOwnerAndIds')
            ->with($this->user, [38, 39])
            ->willReturn([
                $this->createWorkCategory(38)
            ]);

        $constraint = new WorkCategoryOwner;
        $this->validator->validate([38, 39], $constraint);

        $this->buildViolation($constraint->message)->assertRaised();
    }
}
