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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Model\SearchModel;
use App\Application\Service\{
    PaginatorService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\Conversation\Bus\Command\CreateConversationMessage\CreateConversationMessageCommand;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\Conversation\Service\MessageHighlightService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Form\ConversationMessageForm;
use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\ConversationMessage\Repository\Elastica\ElasticaConversationMessageRepository;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Web\Form\SimpleSearchForm;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ConversationDetailHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ConversationMessageFacade $conversationMessageFacade,
        private FormFactoryInterface $formFactory,
        private PaginatorService $paginatorService,
        private SeoPageService $seoPageService,
        private MessageHighlightService $messageHighlightService,
        private ElasticaConversationMessageRepository $elasticaConversationMessageRepository,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Conversation $conversation): Response
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

                $command = CreateConversationMessageCommand::create($conversation, $conversationMessageModel, $user);
                $this->commandBus->dispatch($command);

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

        if ($searchForm->isSubmitted() && $searchForm->isValid() && $searchModel->search) {
            $conversationMessageIds = $this->elasticaConversationMessageRepository
                ->getMessageIdsByConversationAndSearch($conversation, $searchModel->search);

            $conversationMessagesQuery = $this->conversationMessageFacade->queryByIds($conversationMessageIds);
        }

        $conversationMessagesQuery->setHydrationMode(ConversationMessage::class);

        $pagination = $this->paginatorService
            ->createPaginationRequest($request, $conversationMessagesQuery);

        $this->conversationMessageFacade->setIsReadToConversationMessages($pagination, $user);
        $this->seoPageService->setTitle($conversation->getTitle());

        $this->messageHighlightService->addHighlight($pagination, $searchModel->search);

        return $this->twigRenderService->renderToResponse('domain/conversation/detail.html.twig', [
            'conversation' => $conversation,
            'conversationMessages' => $pagination,
            'form' => $form?->createView(),
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
