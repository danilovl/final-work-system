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

namespace App\Domain\ConversationMessageStatus\Bus\Command\UpdateAllToStatus;

use App\Infrastructure\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;

readonly class UpdateAllToStatusHandler implements CommandHandlerInterface
{
    public function __construct(private ConversationMessageStatusFacade $conversationMessageStatusFacade) {}

    public function __invoke(UpdateAllToStatusCommand $command): void
    {
        $this->conversationMessageStatusFacade->updateAllToStatus($command->user, $command->type);
    }
}
