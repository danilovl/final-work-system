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

namespace App\Tests\Unit\Domain\Work\Validator\Constraints;

use App\Domain\Work\Validator\Constraints\{
    EntityExists,
    EntityExistsValidator
};
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityRepository
};
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[AllowMockObjectsWithoutExpectations]
class EntityExistsValidatorTest extends ConstraintValidatorTestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    /** @var MockObject&EntityRepository<object> */
    private MockObject&EntityRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        parent::setUp();
    }

    protected function createValidator(): EntityExistsValidator
    {
        return new EntityExistsValidator($this->entityManager);
    }

    public function testUnexpectedTypeException(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $dummyConstraint = $this->createMock(Constraint::class);
        $this->validator->validate(1, $dummyConstraint);
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new EntityExists(class: stdClass::class));

        $this->assertNoViolation();
    }

    public function testEmptyArrayIsValid(): void
    {
        $this->validator->validate([], new EntityExists(class: stdClass::class));

        $this->assertNoViolation();
    }

    public function testSingleValueExists(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(stdClass::class)
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(new stdClass);

        $this->validator->validate(1, new EntityExists(class: stdClass::class));

        $this->assertNoViolation();
    }

    public function testSingleValueNotExists(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(stdClass::class)
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $constraint = new EntityExists(class: stdClass::class);
        $this->validator->validate(1, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ class }}', stdClass::class)
            ->setParameter('{{ property }}', 'id')
            ->setParameter('{{ value }}', '1')
            ->assertRaised();
    }

    public function testArrayValuesAllExist(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(stdClass::class)
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('count')
            ->with(['id' => [1, 2]])
            ->willReturn(2);

        $this->validator->validate([1, 2], new EntityExists(class: stdClass::class));

        $this->assertNoViolation();
    }

    public function testArrayValuesMissingSome(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(stdClass::class)
            ->willReturn($this->repository);

        $this->repository
            ->expects($this->once())
            ->method('count')
            ->with(['id' => [1, 2]])
            ->willReturn(1);

        $constraint = new EntityExists(class: stdClass::class);
        $this->validator->validate([1, 2], $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ class }}', stdClass::class)
            ->setParameter('{{ property }}', 'id')
            ->setParameter('{{ value }}', '1, 2')
            ->assertRaised();
    }
}
