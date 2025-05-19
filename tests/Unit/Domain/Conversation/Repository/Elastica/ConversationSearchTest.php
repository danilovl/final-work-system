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

namespace Domain\Conversation\Repository\Elastica;

use App\Domain\Conversation\Repository\Elastica\ConversationSearch;
use App\Domain\User\Entity\User;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConversationSearchTest extends TestCase
{
    private ConversationSearch $conversationSearch;

    protected function setUp(): void
    {
        $transformedFinder = $this->createMock(TransformedFinder::class);
        $this->conversationSearch = new ConversationSearch($transformedFinder);
    }

    /**
     * @param int[] $messageIds
     */
    #[DataProvider('createQueryGetIdsByParticipantAndSearchProvider')]
    public function testCreateQueryGetIdsByParticipantAndSearch(
        User $user,
        array $messageIds,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->conversationSearch->createQueryGetIdsByParticipantAndSearch($user, $messageIds, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function createQueryGetIdsByParticipantAndSearchProvider(): Generator
    {
        foreach (['test', 'apple'] as $search) {
            $user = new User;
            $user->setId(random_int(1, 100));

            $messageIds = [1, 2, 3];

            yield [
                $user,
                $messageIds,
                $search,
                [
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
                ]
            ];
        }
    }
}
