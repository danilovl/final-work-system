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

namespace App\Domain\Conversation\Http;

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Application\Model\SearchModel;
use App\Infrastructure\Service\{
    SeoPageService,
    TwigRenderService
};
use App\Domain\Conversation\Bus\Query\ConversationLastMessage\{
    GetConversationLastMessageQuery,
    GetConversationLastMessageQueryResult
};
use App\Domain\Conversation\Entity\Conversation;
use App\Infrastructure\Web\Form\SimpleSearchForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ConversationLastMessageHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private FormFactoryInterface $formFactory,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request, Conversation $conversation): Response
    {
        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        $query = GetConversationLastMessageQuery::create(
            conversation: $conversation,
            search: $searchForm->isSubmitted() && $searchForm->isValid() ? $searchModel->search : null
        );

        /** @var GetConversationLastMessageQueryResult $result */
        $result = $this->queryBus->handle($query);

        $this->seoPageService->setTitle($conversation->getTitle());
        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/conversation/last_message.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'conversation' => $conversation,
            'conversationMessages' => $result->conversationMessages
        ]);
    }
}
