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

namespace App\Domain\Article\Facade;

use App\Domain\ArticleCategory\Repository\ArticleCategoryRepository;
use Doctrine\ORM\Query;

class ArticleCategoryFacade
{
    public function __construct(private ArticleCategoryRepository $articleCategoryRepository)
    {
    }

    public function queryCategoriesByRoles(iterable $roles): Query
    {
        return $this->articleCategoryRepository
            ->allByRoles($roles)
            ->getQuery();
    }
}
