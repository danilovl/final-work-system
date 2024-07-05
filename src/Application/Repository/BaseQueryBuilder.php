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

namespace App\Application\Repository;

use Doctrine\ORM\QueryBuilder;

class BaseQueryBuilder
{
    public function __construct(protected readonly QueryBuilder $queryBuilder) {}

    public function byCallback(callable $callback): self
    {
        $callback($this->queryBuilder);

        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
