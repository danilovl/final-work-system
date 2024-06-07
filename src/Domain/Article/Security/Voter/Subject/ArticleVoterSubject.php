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

namespace App\Domain\Article\Security\Voter\Subject;

use App\Domain\Article\Entity\Article;
use App\Domain\ArticleCategory\Entity\ArticleCategory;

readonly class ArticleVoterSubject
{
    public function __construct(public Article $article, public ArticleCategory $articleCategory)
    {
    }
}
