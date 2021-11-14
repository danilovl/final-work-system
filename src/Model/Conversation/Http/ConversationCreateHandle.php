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

use App\Model\ConversationMessage\Form\ConversationComposeMessageForm;
use App\Helper\{
    UserRoleHelper,
    ConversationHelper
};
use App\Model\Conversation\Facade\{
    ConversationFacade,
    ConversationMessageFacade
};
use App\Model\ConversationMessage\ConversationComposeMessageModel;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ConversationCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ConversationFacade $conversationFacade,
        private ConversationMessageFacade $conversationMessageFacade,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        $conversationParticipants = $this->conversationFacade
            ->getConversationParticipants($user);

        ConversationHelper::getConversationOpposite($conversationParticipants, $user);
        ConversationHelper::usortCzechArray($conversationParticipants);

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
            $this->conversationFacade
                ->processCreateConversation($user, $conversationModel);

            return $this->requestService->redirectToRoute('conversation_list');
        }

        $isUnreadMessages = $this->conversationMessageFacade
            ->isUnreadMessagesByRecipient($user);

        return $this->twigRenderService->render('conversation/create.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'form' => $form->createView()
        ]);
    }
}
