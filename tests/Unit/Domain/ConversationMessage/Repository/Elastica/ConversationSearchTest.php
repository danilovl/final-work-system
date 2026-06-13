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

namespace App\Tests\Unit\Domain\ConversationMessage\Repository\Elastica;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Repository\Elastica\ConversationMessageSearch;
use App\Domain\User\Entity\User;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConversationSearchTest extends TestCase
{
    private ConversationMessageSearch $conversationMessageSearch;

    protected function setUp(): void
    {
        $transformedFinder = $this->createMock(TransformedFinder::class);
        $this->conversationMessageSearch = new ConversationMessageSearch($transformedFinder);
    }

    #[DataProvider('provideCreateQueryGetMessageIdsByConversationAndSearchCases')]
    public function testCreateQueryGetMessageIdsByConversationAndSearch(
        Conversation $conversation,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->conversationMessageSearch->createQueryGetMessageIdsByConversationAndSearch($conversation, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    #[DataProvider('provideCreateQueryGetIdsByParticipantAndSearchCases')]
    public function testCreateQueryGetIdsByParticipantAndSearch(
        User $user,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->conversationMessageSearch->createQueryGetIdsByParticipantAndSearch($user, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function provideCreateQueryGetMessageIdsByConversationAndSearchCases(): Generator
    {
        foreach (['test', 'apple'] as $search) {
            $conversation = new Conversation;
            $conversation->setId(random_int(1, 100));

            yield [
                $conversation,
                $search,
                [
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
                                        ],
                                    ]
                                ]
                            ],
                            'filter' => [
                                'wildcard' => [
                                    'content' => '*' . $search . '*'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
    }

    public static function provideCreateQueryGetIdsByParticipantAndSearchCases(): Generator
    {
        foreach (['test', 'apple'] as $search) {
            $user = new User;
            $user->setId(random_int(1, 100));

            yield [
                $user,
                $search,
                [
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
                ]
            ];
        }
    }
}
