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

namespace App\Model\ConversationMessageStatus\Facade;

use App\Entity\{
    User,
    ConversationMessageStatusType
};
use App\Repository\ConversationMessageStatusRepository;

class ConversationMessageStatusFacade
{
    public function __construct(private ConversationMessageStatusRepository $conversationMessageStatusRepository)
    {
    }

    public function updateAllToStatus(
        User $user,
        ConversationMessageStatusType $conversationMessageStatusType
    ): void {
        $this->conversationMessageStatusRepository->updateAllToStatus($user, $conversationMessageStatusType);
    }
}
