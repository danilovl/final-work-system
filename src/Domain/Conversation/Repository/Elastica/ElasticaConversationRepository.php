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

namespace App\Domain\Conversation\Repository\Elastica;

use App\Domain\User\Entity\User;
use Webmozart\Assert\Assert;

class ElasticaConversationRepository
{
    public function __construct(private readonly ConversationSearch $conversationSearch) {}

    /**
     * @param int[] $conversationIds
     * @return int[]
     */
    public function getIdsByParticipantAndSearch(User $user, array $conversationIds, string $search): array
    {
        Assert::allInteger($conversationIds);

        $result = $this->conversationSearch->getIdsByParticipantAndSearch($user, $conversationIds, $search);

        Assert::allInteger($result);

        return $result;
    }
}
