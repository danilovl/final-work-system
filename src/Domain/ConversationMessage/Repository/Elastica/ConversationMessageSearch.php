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

use App\Domain\ConversationMessage\EventSubscriber\ConversationMessageSearchContentSubscriber;
use App\Application\Traits\Repository\Elastica\ElasticaSearchTrait;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\User\Entity\User;
use Elastica\Index;
use FOS\ElasticaBundle\Finder\TransformedFinder;

readonly class ConversationMessageSearch
{
    use ElasticaSearchTrait;

    private const string CONVERSATION_AGGREGATION = 'conversations';

    private const int SIZE = 1_000;

    public function __construct(
        private TransformedFinder $transformedFinderConversationMessage,
        private Index $indexConversationMessage
    ) {}

    /**
     * @return int[]
     */
    public function getMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        $query = $this->createQueryGetMessageIdsByConversationAndSearch($conversation, $search);
        $results = $this->transformedFinderConversationMessage->findRaw($query);

        return $this->getDocumentIds($results);
    }

    /**
     * @return array{
     *     size: int,
     *     _source: false,
     *     query: array
     * }
     */
    public function createQueryGetMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        return [
            'size' => self::SIZE,
            '_source' => false,
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['conversation.id' => $conversation->getId()]],
                        $this->createContainsQuery(ConversationMessageSearchContentSubscriber::FIELD, $search)
                    ]
                ]
            ]
        ];
    }

    /**
     * @return int[]
     */
    public function getConversationIdsByParticipantAndSearch(User $user, string $search): array
    {
        $query = $this->createQueryGetConversationIdsByParticipantAndSearch($user, $search);
        $resultSet = $this->indexConversationMessage->search($query);
        $aggregation = $resultSet->getAggregation(self::CONVERSATION_AGGREGATION);

        /** @var array<array{key: int|string}> $buckets */
        $buckets = $aggregation['buckets'];

        return array_map(static fn (array $bucket): int => (int) $bucket['key'], $buckets);
    }

    /**
     * @return array{
     *     size: int,
     *     query: array,
     *     aggs: array
     * }
     */
    public function createQueryGetConversationIdsByParticipantAndSearch(User $user, string $search): array
    {
        return [
            'size' => 0,
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['conversation.participants.user.id' => $user->getId()]],
                        $this->createContainsQuery(ConversationMessageSearchContentSubscriber::FIELD, $search)
                    ]
                ]
            ],
            'aggs' => [
                self::CONVERSATION_AGGREGATION => [
                    'terms' => [
                        'field' => 'conversation.id',
                        'size' => self::SIZE
                    ]
                ]
            ]
        ];
    }
}
