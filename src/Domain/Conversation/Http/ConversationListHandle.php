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

use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Infrastructure\Web\Form\Model\SearchModel;
use App\Infrastructure\Service\TwigRenderService;
use App\Domain\Conversation\Bus\Query\ConversationList\{
    GetConversationListQuery,
    GetConversationListQueryResult
};
use App\Domain\User\Service\UserService;
use App\Infrastructure\Web\Form\SimpleSearchForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use App\Domain\ConversationType\Bus\Query\ConversationTypeList\{GetConversationTypeListQuery, GetConversationTypeListQueryResult};
use App\Domain\ConversationMessage\Bus\Query\HasUnreadMessagesByRecipient\{GetHasUnreadMessagesByRecipientQuery, GetHasUnreadMessagesByRecipientQueryResult};

readonly class ConversationListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
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

        $getHasUnreadMessagesByRecipientQuery = GetHasUnreadMessagesByRecipientQuery::create($user);
        /** @var GetHasUnreadMessagesByRecipientQueryResult $getHasUnreadMessagesByRecipientQueryResult */
        $getHasUnreadMessagesByRecipientQueryResult = $this->queryBus->handle($getHasUnreadMessagesByRecipientQuery);
        $isUnreadMessages = $getHasUnreadMessagesByRecipientQueryResult->hasUnread;

        $getConversationTypeListQuery = GetConversationTypeListQuery::create();
        /** @var GetConversationTypeListQueryResult $getConversationTypeListQueryResult */
        $getConversationTypeListQueryResult = $this->queryBus->handle($getConversationTypeListQuery);
        $conversationTypes = $getConversationTypeListQueryResult->conversationTypes;

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
            'messageCounts' => $result->messageCounts,
            'lastMessages' => $result->lastMessages,
            'conversationTypes' => $conversationTypes,
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
