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

namespace App\Model\ConversationMessageStatus;

use App\Entity\ConversationMessageStatusType;
use App\Repository\ConversationMessageStatusRepository;
use App\Entity\User;

class ConversationMessageStatusFacade
{
    private ConversationMessageStatusRepository $conversationMessageStatusRepository;

    public function __construct(ConversationMessageStatusRepository $conversationMessageStatusRepository)
    {
        $this->conversationMessageStatusRepository = $conversationMessageStatusRepository;
    }

    public function updateAllToStatus(
        User $user,
        ConversationMessageStatusType $conversationMessageStatusType
    ): void {
        $this->conversationMessageStatusRepository->updateAllToStatus($user, $conversationMessageStatusType);
    }
}
