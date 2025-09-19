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

namespace App\Domain\ConversationMessage\Repository\Elastica;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\User\Entity\User;
use Webmozart\Assert\Assert;

class ElasticaConversationMessageRepository
{
    public function __construct(private readonly ConversationMessageSearch $conversationSearch) {}

    /**
     * @return int[]
     */
    public function getMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        $result = $this->conversationSearch->getMessageIdsByConversationAndSearch($conversation, $search);

        Assert::allInteger($result);

        return $result;
    }

    /**
     * @return int[]
     */
    public function getMessageIdsByParticipantAndSearch(User $user, string $search): array
    {
        $result = $this->conversationSearch->getMessageIdsByParticipantAndSearch($user, $search);

        Assert::allInteger($result);

        return $result;
    }
}
