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

use App\Application\Form\SimpleSearchForm;
use App\Application\Model\SearchModel;
use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\Conversation\Elastica\ConversationSearch;
use App\Domain\Conversation\Facade\{
    ConversationFacade,
    ConversationMessageFacade
};
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\User\Service\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ConversationListHandle
{
    public function __construct(
        private UserService $userService,
        private ParameterServiceInterface $parameterService,
        private TwigRenderService $twigRenderService,
        private ConversationFacade $conversationFacade,
        private ConversationMessageFacade $conversationMessageFacade,
        private PaginatorService $paginatorService,
        private ConversationSearch $conversationSearch,
        private FormFactoryInterface $formFactory
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        $conversationsQuery = $this->conversationFacade->queryConversationsByParticipantUser($user);

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $conversationIds = $this->conversationSearch->getIdsByParticipantAndSearch($user, $searchModel->search);
            $conversationsQuery = $this->conversationFacade->queryConversationsByIds($conversationIds);
        }

        $pagination = $this->paginatorService->createPaginationRequest(
            $request,
            $conversationsQuery,
            $this->parameterService->getInt('pagination.default.page'),
            $this->parameterService->getInt('pagination.default.limit'),
            ['wrap-queries' => true]
        );

        $this->conversationFacade->setIsReadToConversations($pagination, $user);

        ConversationHelper::getConversationOpposite($pagination, $user);

        $isUnreadMessages = $this->conversationMessageFacade
            ->isUnreadMessagesByRecipient($user);

        return $this->twigRenderService->renderToResponse('conversation/list.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'conversations' => $pagination,
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
