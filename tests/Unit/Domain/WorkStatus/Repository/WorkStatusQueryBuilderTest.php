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

use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Repository\WorkStatusQueryBuilder;
use Doctrine\ORM\{
    EntityManagerInterface,
    QueryBuilder
};
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Generator;

class WorkStatusQueryBuilderTest extends TestCase
{
    #[DataProvider('provideUsersAreBoundByIdCases')]
    public function testUsersAreBoundById(string $type, string $expectedWhere): void
    {
        $queryBuilder = $this->createQueryBuilder();

        new WorkStatusQueryBuilder($queryBuilder)->byUsersAndType(
            type: $type,
            userIds: [36, 155]
        );

        $this->assertSame([36, 155], $queryBuilder->getParameter('userIds')?->getValue());
        $this->assertStringContainsString($expectedWhere, $queryBuilder->getDQL());
    }

    public static function provideUsersAreBoundByIdCases(): Generator
    {
        yield 'author' => [
            'type' => WorkUserTypeConstant::AUTHOR->value,
            'expectedWhere' => 'author.id IN(:userIds)'
        ];

        yield 'opponent' => [
            'type' => WorkUserTypeConstant::OPPONENT->value,
            'expectedWhere' => 'opponent.id IN(:userIds)'
        ];

        yield 'consultant' => [
            'type' => WorkUserTypeConstant::CONSULTANT->value,
            'expectedWhere' => 'consultant.id IN(:userIds)'
        ];
    }

    public function testGroupByUserIdAndName(): void
    {
        $queryBuilder = $this->createQueryBuilder();

        new WorkStatusQueryBuilder($queryBuilder)
            ->selectUserIdNameAndCount(WorkUserTypeConstant::AUTHOR->value)
            ->groupByUserIdAndName();

        $dql = $queryBuilder->getDQL();

        $this->assertStringContainsString('IDENTITY(work.author) AS userId', $dql);
        $this->assertStringContainsString('GROUP BY userId, work_status.name', $dql);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getExpressionBuilder')->willReturn(new Expr);

        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('work_status')->from('WorkStatus', 'work_status');

        return $queryBuilder;
    }
}
