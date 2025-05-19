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
use Elastica\Result;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Webmozart\Assert\Assert;

readonly class ConversationSearch
{
    public function __construct(private TransformedFinder $transformedFinderConversation) {}

    /**
     * @param int[] $messageIds
     * @return int[]
     */
    public function getIdsByParticipantAndSearch(User $user, array $messageIds, string $search): array
    {
        $query = $this->createQueryGetIdsByParticipantAndSearch($user, $messageIds, $search);
        $results = $this->transformedFinderConversation->findRaw($query);

        return $this->getDocumentIds($results);
    }

    /**
     * @param int[] $messageIds
     * @return array{
     *      size: int,
     *      _source: array<string>,
     *      query: array
     *  }
     */
    public function createQueryGetIdsByParticipantAndSearch(User $user, array $messageIds, string $search): array
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
                                'path' => 'participants',
                                'query' => [
                                    'nested' => [
                                        'path' => 'participants.user',
                                        'query' => [
                                            'term' => [
                                                'participants.user.id' => $user->getId()
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'wildcard' => [
                                            'name' => '*' . $search . '*'
                                        ]
                                    ],
                                    [
                                        'nested' => [
                                            'path' => 'messages',
                                            'query' => [
                                                'terms' => [
                                                    'messages.id' => $messageIds
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        'nested' => [
                                            'path' => 'work',
                                            'query' => [
                                                'wildcard' => [
                                                    'work.title' => '*' . $search . '*'
                                                ]
                                            ]
                                        ]
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
