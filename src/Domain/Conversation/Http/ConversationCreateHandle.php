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

use App\Application\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\ConversationType\Bus\Query\ConversationTypeList\{GetConversationTypeListQuery, GetConversationTypeListQueryResult};
use App\Domain\Conversation\Bus\Command\CreateConversation\CreateConversationCommand;
use App\Infrastructure\Service\{
    RequestService,
    TwigRenderService
};
use App\Domain\Conversation\Facade\ConversationFacade;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\ConversationMessage\Form\ConversationComposeMessageForm;
use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use App\Domain\ConversationMessage\Bus\Query\HasUnreadMessagesByRecipient\{GetHasUnreadMessagesByRecipientQuery, GetHasUnreadMessagesByRecipientQueryResult};

readonly class ConversationCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ConversationFacade $conversationFacade,
        private FormFactoryInterface $formFactory,
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $conversationParticipants = $this->conversationFacade
            ->listConversationParticipants($user);

        ConversationHelper::usortCzechArray($conversationParticipants, $user);

        if (UserRoleHelper::isSupervisor($user)) {
            $conversationParticipants = ConversationHelper::groupConversationsByCategorySorting($conversationParticipants);
        }

        $conversationModel = new ConversationComposeMessageModel;
        $form = $this->formFactory
            ->create(ConversationComposeMessageForm::class, $conversationModel, [
                'user' => $user,
                'conversations' => $conversationParticipants
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateConversationCommand::create($user, $conversationModel);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('conversation_list');
        }

        $getHasUnreadMessagesByRecipientQuery = GetHasUnreadMessagesByRecipientQuery::create($user);
        /** @var GetHasUnreadMessagesByRecipientQueryResult $getHasUnreadMessagesByRecipientQueryResult */
        $getHasUnreadMessagesByRecipientQueryResult = $this->queryBus->handle($getHasUnreadMessagesByRecipientQuery);
        $isUnreadMessages = $getHasUnreadMessagesByRecipientQueryResult->hasUnread;

        $getConversationTypeListQuery = GetConversationTypeListQuery::create();
        /** @var GetConversationTypeListQueryResult $getConversationTypeListQueryResult */
        $getConversationTypeListQueryResult = $this->queryBus->handle($getConversationTypeListQuery);
        $conversationTypes = $getConversationTypeListQueryResult->conversationTypes;

        return $this->twigRenderService->renderToResponse('domain/conversation/create.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'form' => $form->createView(),
            'conversationTypes' => $conversationTypes
        ]);
    }
}
