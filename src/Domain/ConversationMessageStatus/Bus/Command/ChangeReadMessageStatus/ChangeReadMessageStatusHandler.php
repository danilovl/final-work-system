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

namespace App\Domain\ConversationMessageStatus\Bus\Command\ChangeReadMessageStatus;

use App\Infrastructure\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Infrastructure\Service\EntityManagerService;

readonly class ChangeReadMessageStatusHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationMessageStatusFacade $conversationMessageStatusFacade
    ) {}

    public function __invoke(ChangeReadMessageStatusCommand $command): void
    {
        $user = $command->user;
        $conversationMessage = $command->conversationMessage;
        $conversation = $conversationMessage->getConversation();

        $conversationMessageStatus = $this->conversationMessageStatusFacade->findByMessageUser(
            conversationMessage: $conversationMessage,
            user: $user
        );

        if ($conversationMessageStatus !== null) {
            switch ($conversationMessageStatus->getType()->getId()) {
                case ConversationMessageStatusTypeConstant::READ->value:
                    /** @var ConversationMessageStatusType $conversationMessageStatusType */
                    $conversationMessageStatusType = $this->entityManagerService->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::UNREAD->value
                    );

                    $conversationMessageStatus->setType($conversationMessageStatusType);

                    break;
                case ConversationMessageStatusTypeConstant::UNREAD->value:
                    /** @var ConversationMessageStatusType $conversationMessageStatusType */
                    $conversationMessageStatusType = $this->entityManagerService->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::READ->value
                    );

                    $conversationMessageStatus->setType($conversationMessageStatusType);

                    break;
            }

            $this->entityManagerService->flush();
        } else {

            /** @var ConversationMessageStatusType $conversationMessageStatusType */
            $conversationMessageStatusType = $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::UNREAD->value
            );

            $newConversationMessageStatus = new ConversationMessageStatus;
            $newConversationMessageStatus->setConversation($conversation);
            $newConversationMessageStatus->setMessage($conversationMessage);
            $newConversationMessageStatus->setUser($user);
            $newConversationMessageStatus->setType($conversationMessageStatusType);

            $this->entityManagerService->persistAndFlush($newConversationMessageStatus);
        }
    }
}
