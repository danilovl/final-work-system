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

namespace App\Model\Article;

use Doctrine\ORM\Query;
use App\Repository\ArticleCategoryRepository;

class ArticleCategoryFacade
{
    private ArticleCategoryRepository $articleCategoryRepository;

    public function __construct(ArticleCategoryRepository $articleCategoryRepository)
    {
        $this->articleCategoryRepository = $articleCategoryRepository;
    }

    public function queryCategoriesByRoles(iterable $roles): Query
    {
        return $this->articleCategoryRepository
            ->allByRoles($roles)
            ->getQuery();
    }
}
