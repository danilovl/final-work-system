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

use App\Model\Conversation\Entity\Conversation;
use App\Model\Conversation\EventDispatcher\ConversationEventDispatcherService;
use App\Model\ConversationMessage\Form\ConversationMessageForm;
use App\Constant\{
    FlashTypeConstant,
    ConversationTypeConstant,
    ConversationMessageStatusTypeConstant
};
use App\Exception\ConstantNotFoundException;
use App\Helper\ConversationHelper;
use App\Model\Conversation\Facade\ConversationMessageFacade;
use App\Model\Conversation\Factory\ConversationFactory;
use App\Model\ConversationMessage\ConversationMessageModel;
use App\Model\ConversationMessage\Factory\ConversationMessageFactory;
use App\Service\{
    UserService,
    PaginatorService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ConversationDetailHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ConversationFactory $conversationFactory,
        private ConversationMessageFactory $conversationMessageFactory,
        private FormFactoryInterface $formFactory,
        private PaginatorService $paginatorService,
        private SeoPageService $seoPageService,
        private ConversationEventDispatcherService $conversationEventDispatcherService
    ) {
    }

    public function handle(Request $request, Conversation $conversation): Response
    {
        $user = $this->userService->getUser();
        $form = null;
        $createForm = false;

        $conversationMessageModel = new ConversationMessageModel;
        $conversationMessageModel->conversation = $conversation;
        $conversationMessageModel->owner = $user;

        switch ($conversation->getType()->getId()) {
            case ConversationTypeConstant::WORK:
                $createForm = true;

                break;
            case ConversationTypeConstant::GROUP:
                if ($conversation->isOwner($user)) {
                    $createForm = true;
                } else {
                    $conversation->setParticipants(null);
                }

                break;
            default:
                throw new ConstantNotFoundException('Conversation type constant not found');
        }

        if ($createForm) {
            $form = $this->formFactory
                ->create(ConversationMessageForm::class, $conversationMessageModel, [
                    'user' => $user
                ])
                ->handleRequest($request);
        }

        if ($form !== null && $form->isSubmitted()) {
            if ($form->isValid()) {
                $conversationMessage = $this->conversationMessageFactory
                    ->flushFromModel($conversationMessageModel);

                $this->conversationFactory->createConversationMessageStatus(
                    $conversation,
                    $conversationMessage,
                    $user,
                    $conversation->getParticipants(),
                    ConversationMessageStatusTypeConstant::UNREAD
                );

                $this->conversationEventDispatcherService
                    ->onConversationMessageCreate($conversationMessage);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
            }
        }

        ConversationHelper::getConversationOpposite([$conversation], $user);

        $conversationMessagesQuery = $this->conversationMessageFacade
            ->queryMessagesByConversation($conversation);

        $pagination = $this->paginatorService
            ->createPaginationRequest($request, $conversationMessagesQuery);

        $this->conversationMessageFacade->setIsReadToConversationMessages($pagination, $user);
        $this->seoPageService->setTitle($conversation->getTitle());

        return $this->twigRenderService->render('conversation/detail.html.twig', [
            'conversation' => $conversation,
            'conversationMessages' => $pagination,
            'form' => $form?->createView()
        ]);
    }
}
