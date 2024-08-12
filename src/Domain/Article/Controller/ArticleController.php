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

namespace App\Domain\Article\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Http\ArticleDetailHandle;
use App\Domain\Article\Security\Voter\Subject\ArticleVoterSubject;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;

readonly class ArticleController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private ArticleDetailHandle $articleDetailHandle
    ) {}

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_article', 'id_category'])]
    public function detail(
        #[MapEntity(mapping: ['id_article' => 'id'])] Article $article,
        #[MapEntity(mapping: ['id_category' => 'id'])] ArticleCategory $articleCategory
    ): Response {
        $articleVoterSubject = new ArticleVoterSubject($article, $articleCategory);

        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $articleVoterSubject);

        return $this->articleDetailHandle->handle($article, $articleCategory);
    }
}
