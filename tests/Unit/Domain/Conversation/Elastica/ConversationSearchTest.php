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

namespace App\Tests\Unit\Domain\Conversation\Elastica;

use App\Domain\Conversation\Elastica\ConversationSearch;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\User\Entity\User;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ConversationSearchTest extends TestCase
{
    private ConversationSearch $conversationSearch;

    protected function setUp(): void
    {
        $transformedFinder = $this->createMock(TransformedFinder::class);
        $this->conversationSearch = new ConversationSearch($transformedFinder);
    }

    #[DataProvider('createQueryGetIdsByParticipantAndSearchProvider')]
    public function testCreateQueryGetMessageIdsByConversationAndSearch(
        User $user,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->conversationSearch->createQueryGetIdsByParticipantAndSearch($user, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function createQueryGetIdsByParticipantAndSearchProvider(): Generator
    {
        foreach (['test', 'apple'] as $search){
            $user = new User;
            $user->setId(random_int(1, 100));

            yield [
                $user,
                $search,
                [
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
                ]
            ];
        }
    }

    #[DataProvider('createQueryGetMessageIdsByConversationAndSearchProvider')]
    public function testCreateQueryGetMessageIdsByConversationAndSearchh(
        Conversation $conversation,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->conversationSearch->createQueryGetMessageIdsByConversationAndSearch($conversation, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function createQueryGetMessageIdsByConversationAndSearchProvider(): Generator
    {
        foreach (['test', 'apple'] as $search){
            $conversation = new Conversation;
            $conversation->setId(random_int(1, 100));

            yield [
                $conversation,
                $search,
                [
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
                                            'messages.content' => '*' . $search . '*'
                                        ]
                                    ],
                                    'inner_hits' => new stdClass
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
    }
}
