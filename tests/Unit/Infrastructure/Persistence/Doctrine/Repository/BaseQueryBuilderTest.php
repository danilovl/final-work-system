<?php declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence\Doctrine\Repository;

use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class BaseQueryBuilderTest extends TestCase
{
    public function testByCallback(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('where');

        $baseQueryBuilder = new BaseQueryBuilder($queryBuilder);

        $callback = static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->where('user.id = :id');
        };

        $baseQueryBuilder->byCallback($callback);
    }

    public function testGetQueryBuilder(): void
    {
        $queryBuilderMock = $this->createStub(QueryBuilder::class);

        $baseQueryBuilder = new BaseQueryBuilder($queryBuilderMock);

        $this->assertSame($queryBuilderMock, $baseQueryBuilder->getQueryBuilder());
    }
}
