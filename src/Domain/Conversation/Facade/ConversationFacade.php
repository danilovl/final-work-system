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

namespace App\Domain\Conversation\Facade;

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\ConversationEventDispatcher;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\Conversation\Repository\ConversationRepository;
use App\Domain\Conversation\Service\ConversationVariationService;
use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Webmozart\Assert\Assert;
use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\ConversationMessage\Bus\Query\ConversationMessage\{GetConversationMessageQuery, GetConversationMessageQueryResult};
use App\Domain\ConversationMessageStatus\Bus\Command\CreateConversationMessageStatus\CreateConversationMessageStatusCommand;

readonly class ConversationFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private QueryBusInterface $queryBus,
        private ConversationVariationService $conversationVariationService,
        private ConversationEventDispatcher $conversationEventDispatcher,
        private ConversationFactory $conversationFactory,
        private ConversationRepository $conversationRepository,
        private CommandBusInterface $commandBus
    ) {}

    public function queryAllByParticipantUser(User $user): Query
    {
        return $this->conversationRepository
            ->allByParticipantUser($user)
            ->getQuery();
    }

    public function queryAllByParticipantUserTypes(User $user, array $types = []): Query
    {
        Assert::allIsInstanceOf($types, ConversationType::class);

        $queryBuilder = $this->conversationRepository
            ->allByParticipantUser($user);

        return $this->conversationRepository
            ->addFilterByTypes($queryBuilder, $types)
            ->getQuery();
    }

    /**
     * @param int[] $ids
     */
    public function queryAllByIds(array $ids): Query
    {
        Assert::allInteger($ids);

        return $this->conversationRepository
            ->allByIds($ids)
            ->getQuery();
    }

    /**
     * @param int[] $workIds
     * @return Conversation[]
     */
    public function findAllByWorkIds(array $workIds): array
    {
        /** @var Conversation[] $result */
        $result = $this->conversationRepository
            ->allByWorkIds($workIds)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findByWorkUser(Work $work, User $user): ?Conversation
    {
        /** @var Conversation|null $result */
        $result = $this->conversationRepository
            ->oneByWorkUser($work, $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return Conversation[]
     */
    public function listConversationParticipants(User $user): array
    {
        $conversationArray = [];
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $workArray = $this->conversationVariationService->getWorkConversationsByUser(
            $user,
            $workStatus
        );

        /** @var Work $work */
        foreach ($workArray as $work) {
            $workUsers = $this->conversationVariationService
                ->getConversationsByWorkUser($work, $user);

            /** @var User $workUser */
            foreach ($workUsers as $workUser) {
                if ($workUser->getId() === $user->getId()) {
                    continue;
                }

                /** @var ConversationType $conversationType */
                $conversationType = $this->entityManagerService->getReference(ConversationType::class, ConversationTypeConstant::WORK->value);

                $newConversation = new Conversation;
                $participants = new ArrayCollection;

                $conversationParticipant = new ConversationParticipant;
                $conversationParticipant->setUser($workUser);
                $participants->add($conversationParticipant);

                $conversationParticipant = new ConversationParticipant;
                $conversationParticipant->setUser($user);
                $participants->add($conversationParticipant);

                $newConversation->setName($work->getTitle());
                $newConversation->setWork($work);
                $newConversation->setType($conversationType);
                $newConversation->setParticipants($participants);

                if (!in_array($newConversation, $conversationArray, true)) {
                    $conversationArray[] = $newConversation;
                }
            }
        }

        return $conversationArray;
    }

    public function processCreateConversation(
        User $user,
        ConversationComposeMessageModel $conversationComposeMessageModel
    ): void {
        /** @var Conversation[] $conversations */
        $conversations = $this->queryAllByParticipantUser($user)->getResult();

        $modelConversation = $conversationComposeMessageModel->getConversations();
        $content = $conversationComposeMessageModel->content;
        $createdNewConversation = false;

        if (count($modelConversation) > 1) {
            $name = $conversationComposeMessageModel->name;
            $conversationParticipantArray = [];
            $conversationParticipantArray[] = $user;

            foreach ($modelConversation as $conversation) {
                $recipient = ConversationHelper::getRecipient($conversation, $user);
                if ($recipient !== null && !in_array($recipient, $conversationParticipantArray, true)) {
                    $conversationParticipantArray[] = $recipient;
                }
            }

            $newConversation = $this->conversationFactory->createConversation(
                owner: $user,
                type: ConversationTypeConstant::GROUP->value,
                name: $name
            );

            $this->conversationFactory->createConversationParticipant(
                conversation: $newConversation,
                participants: $conversationParticipantArray
            );

            $conversationMessage = $this->conversationFactory->createConversationMessage(
                conversation: $newConversation,
                owner: $user,
                content: $content
            );

            $createConversationMessageStatusCommand = CreateConversationMessageStatusCommand::create(
                conversation: $newConversation,
                message: $conversationMessage,
                user: $user,
                participants: $conversationParticipantArray,
                type: ConversationMessageStatusTypeConstant::UNREAD->value
            );
            $this->commandBus->dispatch($createConversationMessageStatusCommand);

            $this->entityManagerService->clear();

            $conversationMessageId = $conversationMessage->getId();
            $getConversationMessageQuery = GetConversationMessageQuery::create($conversationMessageId);
            /** @var GetConversationMessageQueryResult $getConversationMessageQueryResult */
            $getConversationMessageQueryResult = $this->queryBus->handle($getConversationMessageQuery);
            $message = $getConversationMessageQueryResult->conversationMessage;

            $this->conversationEventDispatcher->onConversationMessageCreate($message);
        } else {
            /** @var Conversation $modelConversation */
            $modelConversation = $modelConversation[0];

            foreach ($conversations as $conversation) {
                if ($modelConversation->getWork()?->getId() === $conversation->getWork()?->getId() &&
                    ConversationHelper::getParticipantIds($modelConversation) === ConversationHelper::getParticipantIds($conversation)
                ) {
                    $conversationMessage = $this->conversationFactory
                        ->createConversationMessage(
                            conversation: $conversation,
                            owner: $user,
                            content: $content
                        );

                    $participants = $conversation->getParticipants();
                    $createConversationMessageStatusCommand = CreateConversationMessageStatusCommand::create(
                        conversation: $conversation,
                        message: $conversationMessage,
                        user: $user,
                        participants: $participants,
                        type: ConversationMessageStatusTypeConstant::UNREAD->value
                    );
                    $this->commandBus->dispatch($createConversationMessageStatusCommand);
                    $createdNewConversation = true;

                    $conversationMessageId = $conversationMessage->getId();
                    $getConversationMessageQuery = GetConversationMessageQuery::create($conversationMessageId);
                    /** @var GetConversationMessageQueryResult $getConversationMessageQueryResult */
                    $getConversationMessageQueryResult = $this->queryBus->handle($getConversationMessageQuery);
                    $message = $getConversationMessageQueryResult->conversationMessage;

                    $this->conversationEventDispatcher->onConversationMessageCreate($message);

                    break;
                }
            }

            if ($createdNewConversation) {
                return;
            }

            $name = $modelConversation->getName();
            $work = $modelConversation->getWork();
            $participants = $modelConversation->getParticipants();

            $newConversation = $this->conversationFactory->createConversation(
                $user,
                ConversationTypeConstant::WORK->value,
                $work,
                $name
            );
            $this->conversationFactory
                ->createConversationParticipant($newConversation, $participants);

            $conversationMessage = $this->conversationFactory
                ->createConversationMessage($newConversation, $user, $content);

            $createConversationMessageStatusCommand = CreateConversationMessageStatusCommand::create(
                conversation: $newConversation,
                message: $conversationMessage,
                user: $user,
                participants: $participants,
                type: ConversationMessageStatusTypeConstant::UNREAD->value
            );
            $this->commandBus->dispatch($createConversationMessageStatusCommand);

            $this->entityManagerService->clear();
        }
    }
}
