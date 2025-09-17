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

namespace App\Domain\Conversation\Bus\Command\UpdateAllToStatus;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\ConversationMessageStatus\Repository\ConversationMessageStatusRepository;

readonly class UpdateAllToStatusHandler implements CommandHandlerInterface
{
    public function __construct(private ConversationMessageStatusRepository $conversationMessageStatusRepository) {}

    public function __invoke(UpdateAllToStatusCommand $command): void
    {
        $this->conversationMessageStatusRepository->updateAllToStatus($command->user, $command->type);
    }
}
