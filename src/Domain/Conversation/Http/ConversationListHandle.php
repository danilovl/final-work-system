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
use App\Infrastructure\Service\TwigRenderService;
use App\Domain\Conversation\Bus\Query\ConversationList\{
    GetConversationListQuery,
    GetConversationListQueryResult
};
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\ConversationType\Facade\ConversationTypeFacade;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Web\Form\SimpleSearchForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ConversationListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ConversationTypeFacade $conversationTypeFacade,
        private ConversationMessageFacade $conversationMessageFacade,
        private FormFactoryInterface $formFactory,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        $isUnreadMessages = $this->conversationMessageFacade->isUnreadMessagesByRecipient($user);

        $conversationTypes = $this->conversationTypeFacade->getAll();

        $query = GetConversationListQuery::create(
            request: $request,
            user: $user,
            search: $searchForm->isSubmitted() && $searchForm->isValid() ? $searchModel->search : null,
        );

        /** @var GetConversationListQueryResult $result */
        $result = $this->queryBus->handle($query);

        return $this->twigRenderService->renderToResponse('domain/conversation/list.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'conversations' => $result->conversations,
            'conversationTypes' => $conversationTypes,
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
