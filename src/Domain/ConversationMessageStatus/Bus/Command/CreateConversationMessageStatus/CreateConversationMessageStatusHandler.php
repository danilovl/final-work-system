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

namespace App\Domain\ConversationMessageStatus\Bus\Command\CreateConversationMessageStatus;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Infrastructure\Service\EntityManagerService;

readonly class CreateConversationMessageStatusHandler implements CommandHandlerInterface
{
    public function __construct(private EntityManagerService $entityManagerService) {}

    public function __invoke(CreateConversationMessageStatusCommand $command): void
    {
        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(ConversationMessageStatusType::class, $command->type);

        foreach ($command->participants as $participant) {
            $participantUser = $participant instanceof ConversationParticipant ? $participant->getUser() : $participant;
            if ($participantUser->getId() === $command->user->getId()) {
                continue;
            }

            $messageStatus = new ConversationMessageStatus;
            $messageStatus->setConversation($command->conversation);
            $messageStatus->setMessage($command->message);
            $messageStatus->setType($conversationMessageStatusType);
            $messageStatus->setUser($participantUser);

            $this->entityManagerService->persist($messageStatus);
        }

        $this->entityManagerService->flush();
    }
}
