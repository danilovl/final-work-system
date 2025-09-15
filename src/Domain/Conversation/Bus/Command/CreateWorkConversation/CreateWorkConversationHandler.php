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

namespace App\Domain\Conversation\Bus\Command\CreateWorkConversation;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Factory\ConversationFactory;

readonly class CreateWorkConversationHandler implements CommandHandlerInterface
{
    public function __construct(private ConversationFactory $conversationFactory) {}

    public function __invoke(CreateWorkConversationCommand $command): Conversation
    {
        $conversation = $this->conversationFactory->createConversation(
            $command->userOne,
            $command->type,
            $command->work
        );

        $this->conversationFactory->createConversationParticipant($conversation, [$command->userOne, $command->userTwo]);

        return $conversation;
    }
}
