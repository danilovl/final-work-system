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

namespace App\Tests\Unit\Domain\Work\Repository\Elastica;

use App\Application\Constant\DateFormatConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Repository\Elastica\WorkSearch;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class WorkSearchTest extends KernelTestCase
{
    #[DataProvider('provideCreateQueryCases')]
    public function testCreateQuery(
        User $user,
        string $type,
        array $formData,
        array $expectedQuery
    ): void {
        $form = $this->createStub(FormInterface::class);
        $form->method('getData')->willReturn($formData);
        $form->method('isSubmitted')->willReturn(!empty($formData));

        $transformedFinder = $this->createStub(TransformedFinder::class);

        $workSearch = new WorkSearch(transformedFinderWork: $transformedFinder);

        /** @var array<string, mixed> $data */
        $data = $form->getData();
        $result = $workSearch->createQuery($user, $type, $data);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function provideCreateQueryCases(): Generator
    {
        $user = new User;
        $user->setId(1);

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [],
            [
                'size' => 1_000,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                    'query' => [
                                        'term' => [
                                            WorkUserTypeConstant::SUPERVISOR->value . '.id' => 1
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'nested' => [
                                    'path' => 'status',
                                    'query' => [
                                        'term' => ['status.id' => WorkStatusConstant::ACTIVE->value]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $supervisors = [];
        foreach ([4, 5] as $id) {
            $supervisor = new User;
            $supervisor->setId($id);

            $supervisors[] = $supervisor;
        }

        $opponents = [];
        foreach ([11, 12] as $id) {
            $opponent = new User;
            $opponent->setId($id);

            $opponents[] = $opponent;
        }

        $workStatus = new WorkStatus;
        $workStatus->setId(WorkStatusConstant::ARCHIVE->value);

        $workType = new WorkType;
        $workType->setId(2);

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [
                'title' => 'title text',
                'shortcut' => 'shortcut text',
                'status' => [$workStatus],
                'type' => [$workType],
                'supervisor' => $supervisors,
                'opponent' => $opponents
            ],
            [
                'size' => 1_000,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                    'query' => [
                                        'term' => [
                                            WorkUserTypeConstant::SUPERVISOR->value . '.id' => 1
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'wildcard' => [
                                                'title' => '*title text*'
                                            ]
                                        ],
                                        [
                                            'wildcard' => [
                                                'shortcut' => '*shortcut text*'
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'status',
                                                'query' => [
                                                    'terms' => [
                                                        'status.id' => [WorkStatusConstant::ARCHIVE->value]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'type',
                                                'query' => [
                                                    'terms' => [
                                                        'type.id' => [2]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                                'query' => [
                                                    'terms' => [
                                                        WorkUserTypeConstant::SUPERVISOR->value . '.id' => [4, 5]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => WorkUserTypeConstant::OPPONENT->value,
                                                'query' => [
                                                    'terms' => [
                                                        WorkUserTypeConstant::OPPONENT->value . '.id' => [11, 12]
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

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [
                'title' => 'scalar search',
                'shortcut' => 'SS',
                'status' => [WorkStatusConstant::ARCHIVE->value],
                'type' => [2],
                'supervisor' => [4, 5],
                'opponent' => [11, 12],
                'deadline' => ['2026-05-30']
            ],
            [
                'size' => 1_000,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                    'query' => [
                                        'term' => [
                                            WorkUserTypeConstant::SUPERVISOR->value . '.id' => 1
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'wildcard' => [
                                                'title' => '*scalar search*'
                                            ]
                                        ],
                                        [
                                            'wildcard' => [
                                                'shortcut' => '*ss*'
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'status',
                                                'query' => [
                                                    'terms' => [
                                                        'status.id' => [WorkStatusConstant::ARCHIVE->value]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'type',
                                                'query' => [
                                                    'terms' => [
                                                        'type.id' => [2]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                                'query' => [
                                                    'terms' => [
                                                        WorkUserTypeConstant::SUPERVISOR->value . '.id' => [4, 5]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => WorkUserTypeConstant::OPPONENT->value,
                                                'query' => [
                                                    'terms' => [
                                                        WorkUserTypeConstant::OPPONENT->value . '.id' => [11, 12]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            'bool' => [
                                'should' => [
                                    [
                                        'range' => [
                                            'deadline' => [
                                                'gte' => '2026-05-30||-1d',
                                                'lte' => '2026-05-30||+1d',
                                                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE->value
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

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [
                'title' => 'single scalar search',
                'status' => WorkStatusConstant::ARCHIVE->value,
                'type' => 2,
                'author' => 10
            ],
            [
                'size' => 1_000,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                    'query' => [
                                        'term' => [
                                            WorkUserTypeConstant::SUPERVISOR->value . '.id' => 1
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'wildcard' => [
                                                'title' => '*single scalar search*'
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'status',
                                                'query' => [
                                                    'term' => [
                                                        'status.id' => WorkStatusConstant::ARCHIVE->value
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'type',
                                                'query' => [
                                                    'term' => [
                                                        'type.id' => 2
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => 'author',
                                                'query' => [
                                                    'term' => [
                                                        'author.id' => 10
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

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [
                'title' => 'syst'
            ],
            [
                'size' => 1_000,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                    'query' => [
                                        'term' => [
                                            WorkUserTypeConstant::SUPERVISOR->value . '.id' => 1
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'wildcard' => [
                                                'title' => '*syst*'
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

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [
                'title' => 'Systém '
            ],
            [
                'size' => 1_000,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'path' => WorkUserTypeConstant::SUPERVISOR->value,
                                    'query' => [
                                        'term' => [
                                            WorkUserTypeConstant::SUPERVISOR->value . '.id' => 1
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'wildcard' => [
                                                'title' => '*system*'
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
