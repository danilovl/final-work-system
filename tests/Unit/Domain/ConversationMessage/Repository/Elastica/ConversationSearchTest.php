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
use Elastica\Index;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConversationSearchTest extends TestCase
{
    private ConversationMessageSearch $conversationMessageSearch;

    protected function setUp(): void
    {
        $transformedFinder = $this->createStub(TransformedFinder::class);
        $index = $this->createStub(Index::class);
        $this->conversationMessageSearch = new ConversationMessageSearch($transformedFinder, $index);
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

    #[DataProvider('provideCreateQueryGetConversationIdsByParticipantAndSearchCases')]
    public function testCreateQueryGetConversationIdsByParticipantAndSearch(
        User $user,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->conversationMessageSearch->createQueryGetConversationIdsByParticipantAndSearch($user, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function provideCreateQueryGetMessageIdsByConversationAndSearchCases(): Generator
    {
        foreach (['test' => 'test', 'Zpráva' => 'zprava'] as $search => $expectedSearch) {
            $conversation = new Conversation;
            $conversation->setId(random_int(1, 100));

            yield $search => [
                'conversation' => $conversation,
                'search' => $search,
                'expectedQuery' => [
                    'size' => 1_000,
                    '_source' => false,
                    'query' => [
                        'bool' => [
                            'filter' => [
                                ['term' => ['conversation.id' => $conversation->getId()]],
                                ['wildcard' => ['content_search' => '*' . $expectedSearch . '*']]
                            ]
                        ]
                    ]
                ]
            ];
        }
    }

    public static function provideCreateQueryGetConversationIdsByParticipantAndSearchCases(): Generator
    {
        foreach (['test' => 'test', 'Zpráva' => 'zprava'] as $search => $expectedSearch) {
            $user = new User;
            $user->setId(random_int(1, 100));

            yield $search => [
                'user' => $user,
                'search' => $search,
                'expectedQuery' => [
                    'size' => 0,
                    'query' => [
                        'bool' => [
                            'filter' => [
                                ['term' => ['conversation.participants.user.id' => $user->getId()]],
                                ['wildcard' => ['content_search' => '*' . $expectedSearch . '*']]
                            ]
                        ]
                    ],
                    'aggs' => [
                        'conversations' => [
                            'terms' => [
                                'field' => 'conversation.id',
                                'size' => 1_000
                            ]
                        ]
                    ]
                ]
            ];
        }
    }
}
