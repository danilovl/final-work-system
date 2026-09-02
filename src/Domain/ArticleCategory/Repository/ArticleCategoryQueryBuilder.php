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

namespace App\Domain\ArticleCategory\Repository;

use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;

class ArticleCategoryQueryBuilder extends BaseQueryBuilder
{
    public function distinct(): self
    {
        $this->queryBuilder->distinct();

        return $this;
    }

    public function byRoles(iterable $roles): self
    {
        $i = 0;
        foreach ($roles as $role) {
            $parameterName = "value_$i";
            $condition = "article_category.access LIKE :$parameterName";

            if ($i === 0) {
                $this->queryBuilder->andWhere($condition);
            } else {
                $this->queryBuilder->orWhere($condition);
            }

            $this->queryBuilder->setParameter($parameterName, "%$role%");
            $i++;
        }

        return $this;
    }

    public function byActive(bool $active): self
    {
        $this->queryBuilder
            ->andWhere('article_category.active = :active')
            ->setParameter('active', $active);

        return $this;
    }
}
