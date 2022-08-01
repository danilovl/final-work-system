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

use App\Application\Helper\{
    UserRoleHelper
};
use App\Application\Helper\ConversationHelper;
use App\Application\Service\{
    TwigRenderService
};
use App\Application\Service\RequestService;
use App\Application\Service\UserService;
use App\Domain\Conversation\Facade\{
    ConversationMessageFacade
};
use App\Domain\Conversation\Facade\ConversationFacade;
use App\Domain\ConversationMessage\ConversationComposeMessageModel;
use App\Domain\ConversationMessage\Form\ConversationComposeMessageForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ConversationCreateHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly TwigRenderService $twigRenderService,
        private readonly ConversationFacade $conversationFacade,
        private readonly ConversationMessageFacade $conversationMessageFacade,
        private readonly FormFactoryInterface $formFactory
    ) {}

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
