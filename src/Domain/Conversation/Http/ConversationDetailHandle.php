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

use App\Infrastructure\Exception\ConstantNotFoundException;
use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByIds\{GetConversationMessageQueryByIdsQuery, GetConversationMessageQueryByIdsQueryResult};
use App\Domain\ConversationMessage\Bus\Query\MessageIdsByConversationAndSearch\{GetMessageIdsByConversationAndSearchQuery, GetMessageIdsByConversationAndSearchQueryResult};
use App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByConversation\{GetConversationMessageQueryByConversationQuery, GetConversationMessageQueryByConversationQueryResult};
use App\Infrastructure\Web\Form\Model\SearchModel;
use App\Infrastructure\Service\{
    PaginatorService,
    RequestService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\ConversationMessage\Bus\Command\CreateConversationMessage\CreateConversationMessageCommand;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Service\MessageHighlightService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Form\ConversationMessageForm;
use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Web\Form\SimpleSearchForm;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ConversationDetailHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private QueryBusInterface $queryBus,
        private FormFactoryInterface $formFactory,
        private PaginatorService $paginatorService,
        private SeoPageService $seoPageService,
        private MessageHighlightService $messageHighlightService,
        private CommandBusInterface $commandBus,
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService
    ) {}

    public function __invoke(Request $request, Conversation $conversation): Response
    {
        $user = $this->userService->getUser();
        $form = null;
        $createForm = false;
        $participants = $conversation->getParticipants();

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
                    $participants = new ArrayCollection;
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

        if ($form !== null && $form->isSubmitted() && $form->isValid()) {
            $conversation->createUpdateAblePreUpdate();

            $command = CreateConversationMessageCommand::create($conversation, $conversationMessageModel, $user);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('conversation_detail', [
                'id' => $this->hashidsService->encode($conversation->getId())
            ]);
        }

        $getConversationMessageQueryByConversationQuery = GetConversationMessageQueryByConversationQuery::create($conversation);
        /** @var GetConversationMessageQueryByConversationQueryResult $getConversationMessageQueryByConversationQueryResult */
        $getConversationMessageQueryByConversationQueryResult = $this->queryBus->handle($getConversationMessageQueryByConversationQuery);
        $conversationMessagesQuery = $getConversationMessageQueryByConversationQueryResult->query;

        $searchModel = new SearchModel;
        $searchForm = $this->formFactory
            ->create(SimpleSearchForm::class, $searchModel)
            ->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid() && $searchModel->search) {
            $getMessageIdsByConversationAndSearchQuery = GetMessageIdsByConversationAndSearchQuery::create(
                conversation: $conversation,
                search: $searchModel->search
            );
            /** @var GetMessageIdsByConversationAndSearchQueryResult $getMessageIdsByConversationAndSearchQueryResult */
            $getMessageIdsByConversationAndSearchQueryResult = $this->queryBus->handle($getMessageIdsByConversationAndSearchQuery);
            $conversationMessageIds = $getMessageIdsByConversationAndSearchQueryResult->ids;

            $getConversationMessageQueryByIdsQuery = GetConversationMessageQueryByIdsQuery::create($conversationMessageIds);
            /** @var GetConversationMessageQueryByIdsQueryResult $getConversationMessageQueryByIdsQueryResult */
            $getConversationMessageQueryByIdsQueryResult = $this->queryBus->handle($getConversationMessageQueryByIdsQuery);
            $conversationMessagesQuery = $getConversationMessageQueryByIdsQueryResult->query;
        }

        $conversationMessagesQuery->setHydrationMode(ConversationMessage::class);

        $pagination = $this->paginatorService
            ->createPaginationRequest($request, $conversationMessagesQuery);

        $this->seoPageService->setTitle($conversation->getTitle());

        $this->messageHighlightService->addHighlight($pagination, $searchModel->search);

        return $this->twigRenderService->renderToResponse('domain/conversation/detail.html.twig', [
            'conversation' => $conversation,
            'participants' => $participants,
            'conversationMessages' => $pagination,
            'form' => $form?->createView(),
            'searchForm' => $searchForm->createView(),
            'enableClearSearch' => !empty($searchModel->search)
        ]);
    }
}
