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

use App\Application\Constant\{
    FlashTypeConstant
};
use App\Application\Constant\ConversationMessageStatusTypeConstant;
use App\Application\Constant\ConversationTypeConstant;
use App\Application\Exception\ConstantNotFoundException;
use App\Application\Helper\ConversationHelper;
use App\Application\Service\{
    RequestService,
    UserService,
    SeoPageService,
    PaginatorService,
    TwigRenderService
};
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\ConversationEventDispatcherService;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\ConversationMessage\ConversationMessageModel;
use App\Domain\ConversationMessage\Factory\ConversationMessageFactory;
use App\Domain\ConversationMessage\Form\ConversationMessageForm;
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