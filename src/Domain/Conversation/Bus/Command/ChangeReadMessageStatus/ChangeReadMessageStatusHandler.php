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

namespace App\Domain\Conversation\Bus\Command\ChangeReadMessageStatus;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Conversation\Facade\ConversationMessageFacade;

readonly class ChangeReadMessageStatusHandler implements CommandHandlerInterface
{
    public function __construct(private ConversationMessageFacade $conversationMessageFacade) {}

    public function __invoke(ChangeReadMessageStatusCommand $command): void
    {
        $this->conversationMessageFacade->changeReadMessageStatus(
            $command->user,
            $command->conversationMessage
        );
    }
}
