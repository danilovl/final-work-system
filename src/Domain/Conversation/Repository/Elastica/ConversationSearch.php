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

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\User\Entity\User;
use Elastica\Result;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use stdClass;

class ConversationSearch
{
    public function __construct(private TransformedFinder $transformedFinderConversation) {}

    public function getIdsByParticipantAndSearch(User $user, string $search): array
    {
        $query = $this->createQueryGetIdsByParticipantAndSearch($user, $search);
        $results = $this->transformedFinderConversation->findRaw($query);

        return array_map(static fn (Result $document): int => (int) $document->getId(), $results);
    }

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
                                                'wildcard' => [
                                                    'messages.content' => '*' . $search . '*'
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

    public function getMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        $query = $this->createQueryGetMessageIdsByConversationAndSearch($conversation, $search);
        $results = $this->transformedFinderConversation->findRaw($query);

        $messageIds = [];
        foreach ($results as $result) {
            $innerHits = $result->getInnerHits();
            foreach ($innerHits['messages'] as $innerHit) {
                foreach ($innerHit['hits'] as $hit) {
                    $messageIds[] = $hit['_source']['id'];
                }

            }
        }

        return $messageIds;
    }

    public function createQueryGetMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        return [
            'size' => 1_000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'id' => $conversation->getId()
                            ]
                        ],
                    ],
                    'filter' => [
                        'nested' => [
                            'path' => 'messages',
                            'query' => [
                                'wildcard' => [
                                    'messages.content' => '*' . $this->transformSearch($search) . '*'
                                ]
                            ],
                            'inner_hits' => new stdClass
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
}
