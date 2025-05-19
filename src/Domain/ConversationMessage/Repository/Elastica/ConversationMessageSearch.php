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
use Elastica\Result;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Webmozart\Assert\Assert;

readonly class ConversationMessageSearch
{
    public function __construct(private TransformedFinder $transformedFinderConversationMessage) {}

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
     *     query: array
     * }
     */
    public function createQueryGetMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        return [
            'size' => 1_000,
            '_source' => ['id'],
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'nested' => [
                                'path' => 'conversation',
                                'query' => [
                                    'term' => [
                                        'conversation.id' => $conversation->getId()
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'filter' => [
                        'wildcard' => [
                            'content' => '*' . $this->transformSearch($search) . '*'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return int[]
     */
    public function getMessageIdsByParticipantAndSearch(User $user, string $search): array
    {
        $query = $this->createQueryGetIdsByParticipantAndSearch($user, $search);
        $results = $this->transformedFinderConversationMessage->findRaw($query);

        return $this->getDocumentIds($results);
    }

    /**
     * @return array{
     *     size: int,
     *     _source: array<string>,
     *     query: array
     * }
     */
    public function createQueryGetIdsByParticipantAndSearch(User $user, string $search): array
    {
        $search = $this->transformSearch($search);

        return [
            'size' => 1_000,
            '_source' => ['id'],
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'nested' => [
                                'path' => 'conversation',
                                'query' => [
                                    'nested' => [
                                        'path' => 'conversation.participants',
                                        'query' => [
                                            'nested' => [
                                                'path' => 'conversation.participants.user',
                                                'query' => [
                                                    'term' => [
                                                        'conversation.participants.user.id' => $user->getId()
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'bool' => [
                                'should' => [
                                    'wildcard' => [
                                        'content' => '*' . $search . '*'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function transformSearch(string $search): string
    {
        return mb_strtolower($search);
    }

    /**
     * @param Result[] $results
     * @return int[]
     */
    private function getDocumentIds(array $results): array
    {
        $messageIds = array_map(static fn (Result $document): int => (int) $document->getId(), $results);

        Assert::allInteger($messageIds);

        return $messageIds;
    }
}
