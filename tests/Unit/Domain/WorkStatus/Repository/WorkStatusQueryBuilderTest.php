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

namespace App\Tests\Unit\Domain\WorkStatus\Repository;

use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Repository\WorkStatusQueryBuilder;
use Doctrine\ORM\{
    EntityManagerInterface,
    QueryBuilder
};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Generator;

class WorkStatusQueryBuilderTest extends TestCase
{
    #[DataProvider('provideUserIsBoundByIdCases')]
    public function testUserIsBoundById(string $type, string $expectedWhere): void
    {
        $queryBuilder = new QueryBuilder($this->createStub(EntityManagerInterface::class));
        $queryBuilder->select('work_status')->from('WorkStatus', 'work_status');

        $user = new User;
        $user->setId(36);

        new WorkStatusQueryBuilder($queryBuilder)->byUserAndType($type, $user);

        $this->assertSame(36, $queryBuilder->getParameter('user')?->getValue());
        $this->assertStringContainsString($expectedWhere, $queryBuilder->getDQL());
    }

    /**
     * Users from the paginator are runtime DTO subclasses of User (doctrine-entity-dto-bundle).
     * Doctrine cannot map such a class, so the id has to be bound, not the object.
     */
    public function testRuntimeSubclassOfUserIsBoundById(): void
    {
        $queryBuilder = new QueryBuilder($this->createStub(EntityManagerInterface::class));
        $queryBuilder->select('work_status')->from('WorkStatus', 'work_status');

        $user = new class() extends User {};
        $user->setId(155);

        new WorkStatusQueryBuilder($queryBuilder)->byUserAndType(WorkUserTypeConstant::AUTHOR->value, $user);

        $this->assertSame(155, $queryBuilder->getParameter('user')?->getValue());
    }

    public function testWithoutTypeOrUserNothingIsAdded(): void
    {
        $queryBuilder = new QueryBuilder($this->createStub(EntityManagerInterface::class));

        new WorkStatusQueryBuilder($queryBuilder)->byUserAndType(null, new User);
        new WorkStatusQueryBuilder($queryBuilder)->byUserAndType(WorkUserTypeConstant::AUTHOR->value, null);

        $this->assertNull($queryBuilder->getParameter('user'));
        $this->assertNull($queryBuilder->getDQLPart('where'));
    }

    public static function provideUserIsBoundByIdCases(): Generator
    {
        yield [WorkUserTypeConstant::AUTHOR->value, 'author = :user'];
        yield [WorkUserTypeConstant::OPPONENT->value, 'opponent = :user'];
        yield [WorkUserTypeConstant::CONSULTANT->value, 'consultant = :user'];
    }
}
