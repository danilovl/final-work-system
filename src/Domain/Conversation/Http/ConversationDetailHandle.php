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

use App\Application\Constant\FlashTypeConstant;
use App\Application\Exception\ConstantNotFoundException;
use App\Application\Form\SimpleSearchForm;
use App\Application\Model\SearchModel;
use App\Application\Service\{
    PaginatorService,
    RequestService,
    SeoPageService,
    TwigRenderService,};
use App\Domain\Conversation\Elastica\ConversationSearch;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\ConversationEventDispatcherService;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\Conversation\Service\MessageHighlightService;
use App\Domain\ConversationMessage\Factory\ConversationMessageFactory;
use App\Domain\ConversationMessage\Form\ConversationMessageForm;
use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\User\Service\UserService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response};

readonly class ConversationDetailHandle
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
        private ConversationEventDispatcherService $conversationEventDispatcherService,
        private ConversationSearch $conversationSearch,
        private MessageHighlightService $messageHighlightService
    ) {}

    public function handle(Request $request, Conversation $conversation): Response
    {
        $user = $this->userService->getUser();
        $form = null;
        $createForm = false;

        $conversationMessageModel = new ConversationMessageModel;
        $conversationMessageModel->conversation = $conversation;
        $conversationMessageModel->owner = $user;

        switch ($conversation->getType()->getId()) {
            case ConversationTypeConstant::WORK->value:
                $createForm = true;

                break;
            case ConversationTypeConstant::GROUP->value:
                if ($conversation->isOwner($user)) {
                    $createForm = true;
                } else {
                    $conversation->setParticipants(new ArrayCollection);
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
                $conversation->createUpdateAblePreUpdate();

                $conversationMessage = $this->conversationMessageFactory
                    ->flushFromModel($conversationMessageModel);

                $this->conversationFactory->createConversationMessageStatus(
                    $conversation,
                    $conversationMessage,
                    $user,
                    $conversation->getParticipants(),
                    ConversationMessageStatusTypeConstant::UNREAD->value
                );

                $this->conversationEventDispatcherService
                    ->onConversationMessageCreate($conversationMessage);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');
            } else {
                $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
                $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
            }
        }

        ConversationHelper::getConversationOpposite([$conversation], $user);

        $conversationMessagesQuery = $this->conversationMessageFacade
            ->queryMessagesByConversation($conversation);

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $conversationMessageIds = $this->conversationSearch->getMessageIdsByConversationAndSearch($conversation, $searchModel->search);
            $conversationMessagesQuery = $this->conversationMessageFacade->queryByIds($conversationMessageIds);
        }

        $pagination = $this->paginatorService
            ->createPaginationRequest($request, $conversationMessagesQuery);

        $this->conversationMessageFacade->setIsReadToConversationMessages($pagination, $user);
        $this->seoPageService->setTitle($conversation->getTitle());

        $this->messageHighlightService->addHighlight($pagination, $searchModel);

        return $this->twigRenderService->render('conversation/detail.html.twig', [
            'conversation' => $conversation,
            'conversationMessages' => $pagination,
            'form' => $form?->createView(),
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
