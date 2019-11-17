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

namespace FinalWork\FinalWorkBundle\Security\Voter\Subject;

use FinalWork\FinalWorkBundle\Entity\{
    Article,
    ArticleCategory
};

class ArticleVoterSubject
{
    /**
     * @var Article|null
     */
    protected $article;

    /**
     * @var ArticleCategory|null
     */
    protected $articleCategory;

    /**
     * @return Article|null
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * @param Article|null $article
     * @return ArticleVoterSubject
     */
    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return ArticleCategory|null
     */
    public function getArticleCategory(): ?ArticleCategory
    {
        return $this->articleCategory;
    }

    /**
     * @param ArticleCategory|null $articleCategory
     * @return ArticleVoterSubject
     */
    public function setArticleCategory(?ArticleCategory $articleCategory): self
    {
        $this->articleCategory = $articleCategory;

        return $this;
    }
}