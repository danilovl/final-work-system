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

namespace App\Domain\Conversation\Bus\Command\CreateConversation;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Conversation\Facade\ConversationFacade;

readonly class CreateConversationHandler implements CommandHandlerInterface
{
    public function __construct(private ConversationFacade $conversationFacade) {}

    public function __invoke(CreateConversationCommand $command): void
    {
        $this->conversationFacade->processCreateConversation($command->user, $command->conversationComposeMessageModel);
    }
}
