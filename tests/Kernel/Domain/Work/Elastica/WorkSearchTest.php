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

namespace App\Tests\Kernel\Domain\Work\Elastica;

use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Elastica\WorkSearch;
use App\Domain\Work\Form\WorkSearchForm;
use App\Domain\WorkSearch\Model\WorkSearchModel;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use DateTime;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class WorkSearchTest extends KernelTestCase
{
    private FormInterface|WorkSearchForm $workSearchForm;
    private WorkSearch $workSearch;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $formFactory = $kernel->getContainer()->get('form.factory');

        $users = array_map(static function (int $id): User {
            $user = new User;
            $user->setId($id);

            return $user;
        }, range(0, 10));

        $this->workSearchForm = $formFactory->create(WorkSearchForm::class, new WorkSearchModel, [
            'authors' => [],
            'opponents' => $users,
            'consultants' => $users,
            'supervisors' => $users,
            'deadlines' => [new DateTime('2023-01-01')]
        ]);

        $transformedFinder = $this->createMock(TransformedFinder::class);
        $this->workSearch = new WorkSearch($transformedFinder);
    }

    #[DataProvider('createQueryProvider')]
    public function testCreateQuery(
        User $user,
        string $type,
        ?array $formData,
        array $expectedQuery
    ): void {
        if ($formData !== null) {
            $this->workSearchForm->submit($formData);
        }

        $result = $this->workSearch->createQuery($user, $type, $this->workSearchForm);

        $this->assertEquals($expectedQuery, $result);
    }

    public static function createQueryProvider(): Generator
    {
        $user = new User;
        $user->setId(1);

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            null,
            [
                'size' => 1000,
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

        yield [
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
            [
                'title' => 'title text',
                'shortcut' => 'shortcut text',
                'status' => [WorkStatusConstant::ARCHIVE->value],
                'type' => [2],
                'supervisor' => [4],
                'opponent' => [5]
            ],
            [
                'size' => 1000,
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
                                            'match' => [
                                                'title' => 'title text'
                                            ]
                                        ],
                                        [
                                            'match' => [
                                                'shortcut' => 'shortcut text'
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
                                                        WorkUserTypeConstant::SUPERVISOR->value . '.id' => [4]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'nested' => [
                                                'path' => WorkUserTypeConstant::OPPONENT->value,
                                                'query' => [
                                                    'terms' => [
                                                        WorkUserTypeConstant::OPPONENT->value . '.id' => [5]
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
