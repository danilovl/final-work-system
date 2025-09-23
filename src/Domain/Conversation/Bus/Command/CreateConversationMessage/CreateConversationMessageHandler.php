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

namespace App\Domain\Conversation\Bus\Command\CreateConversationMessage;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Conversation\EventDispatcher\ConversationEventDispatcher;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\ConversationMessage\Factory\ConversationMessageFactory;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;

readonly class CreateConversationMessageHandler implements CommandHandlerInterface
{
    public function __construct(
        private ConversationFactory $conversationFactory,
        private ConversationMessageFactory $conversationMessageFactory,
        private ConversationEventDispatcher $conversationEventDispatcher
    ) {}

    public function __invoke(CreateConversationMessageCommand $command): void
    {
        $conversationMessage = $this->conversationMessageFactory->flushFromModel($command->conversationMessageModel);

        $this->conversationFactory->createConversationMessageStatus(
            $command->conversation,
            $conversationMessage,
            $command->user,
            $command->conversation->getParticipants(),
            ConversationMessageStatusTypeConstant::UNREAD->value
        );

        $this->conversationEventDispatcher->onConversationMessageCreate($conversationMessage);
    }
}
