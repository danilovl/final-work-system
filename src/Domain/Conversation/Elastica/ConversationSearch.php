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

namespace App\Domain\Conversation\Elastica;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\User\Entity\User;
use Elastica\Result;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use stdClass;

class ConversationSearch
{
    public function __construct(private readonly TransformedFinder $transformedFinderConversation) {}

    public function getIdsByParticipantAndSearch(User $user, string $search): array
    {
        $search = mb_strtolower($search);

        $query = [
            'size' => 1000,
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

        $results = $this->transformedFinderConversation->findRaw($query);

        return array_map(static fn(Result $document): int => (int) $document->getId(), $results);
    }

    public function getMessageIdsByConversationAndSearch(Conversation $conversation, string $search): array
    {
        $query = [
            'size' => 1000,
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
                                    'messages.content' => '*' . mb_strtolower($search) . '*'
                                ]
                            ],
                            'inner_hits' => new stdClass()
                        ]
                    ]
                ]
            ]
        ];

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
}
