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

namespace App\Model\Conversation\Http;

use App\Helper\ConversationHelper;
use App\Model\Conversation\Facade\{
    ConversationFacade,
    ConversationMessageFacade
};
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Service\{
    UserService,
    PaginatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ConversationListHandle
{
    public function __construct(
        private UserService $userService,
        private ParameterServiceInterface $parameterService,
        private TwigRenderService $twigRenderService,
        private ConversationFacade $conversationFacade,
        private ConversationMessageFacade $conversationMessageFacade,
        private PaginatorService $paginatorService
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        $conversationsQuery = $this->conversationFacade
            ->queryConversationsByUser($user);

        $pagination = $this->paginatorService->createPaginationRequest(
            $request,
            $conversationsQuery,
            $this->parameterService->get('pagination.default.page'),
            $this->parameterService->get('pagination.default.limit'),
            ['wrap-queries' => true]
        );

        $this->conversationFacade
            ->setIsReadToConversations($pagination, $user);

        ConversationHelper::getConversationOpposite($pagination, $user);

        $isUnreadMessages = $this->conversationMessageFacade
            ->isUnreadMessagesByRecipient($user);

        return $this->twigRenderService->render('conversation/list.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'conversations' => $pagination
        ]);
    }
}
