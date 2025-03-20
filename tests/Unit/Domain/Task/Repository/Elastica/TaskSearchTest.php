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

namespace Domain\Task\Repository\Elastica;

use App\Domain\Task\Repository\Elastica\TaskSearch;
use App\Domain\User\Entity\User;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TaskSearchTest extends TestCase
{
    private TaskSearch $taskSearch;

    protected function setUp(): void
    {
        $transformedFinder = $this->createMock(TransformedFinder::class);
        $this->taskSearch = new TaskSearch($transformedFinder);
    }

    #[DataProvider('createQueryProvider')]
    public function testCreateQuery(
        User $user,
        string $search,
        array $expectedQuery
    ): void {
        $result = $this->taskSearch->createQuery($user, $search);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function createQueryProvider(): Generator
    {
        $user = new User;
        $user->setId(1);
        $search = 'test';

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
                                    'path' => 'owner',
                                    'query' => [
                                        'term' => [
                                            'owner.id' => $user->getId()
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
                                            'wildcard' => [
                                                'description' => '*' . $search . '*'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $user = new User;
        $user->setId(1_000);
        $search = 'apple';

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
                                    'path' => 'owner',
                                    'query' => [
                                        'term' => [
                                            'owner.id' => $user->getId()
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
                                            'wildcard' => [
                                                'description' => '*' . $search . '*'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
