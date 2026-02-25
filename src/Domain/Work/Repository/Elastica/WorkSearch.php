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

namespace App\Domain\Work\Repository\Elastica;

use App\Application\Constant\DateFormatConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use ArrayIterator;
use Collator;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\ElasticaBundle\Finder\TransformedFinder;

readonly class WorkSearch
{
    public function __construct(private TransformedFinder $transformedFinderWork) {}

    /**
     * @param array<string, mixed> $filters
     * @return ArrayIterator<int|string, Work>
     */
    public function filterWorkList(
        User $user,
        string $type,
        array $filters
    ): ArrayIterator {
        $query = $this->createQuery($user, $type, $filters);

        /** @var Work[] $works */
        $works = $this->transformedFinderWork->find($query);
        $works = new ArrayCollection($works);

        $collator = new Collator('cs_CZ.UTF-8');
        /** @var ArrayIterator<int|string, Work> $iterator */
        $iterator = $works->getIterator();

        $iterator->uasort(static function (Work $first, Work $second) use ($collator): int {
            /** @var int $result */
            $result = $collator->compare(
                $first->getAuthor()->getLastname(),
                $second->getAuthor()->getLastname()
            );

            return $result;
        });

        return $iterator;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function createQuery(
        User $user,
        string $type,
        array $filters
    ): array {
        $query = [
            'size' => 1_000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'nested' => [
                                'path' => $type,
                                'query' => [
                                    'term' => ["{$type}.id" => $user->getId()]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if (empty($filters)) {
            $query['query']['bool']['must'][] = [
                'nested' => [
                    'path' => 'status',
                    'query' => [
                        'term' => ['status.id' => WorkStatusConstant::ACTIVE->value]
                    ]
                ]
            ];
        }

        $filter = [];
        $filterDates = [];

        foreach ($filters as $field => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_iterable($value) && is_countable($value) && count($value) === 0) {
                continue;
            }

            if (is_iterable($value)) {
                $filedValues = [];

                foreach ($value as $item) {
                    if ($field === 'deadline') {
                        $filedValues[] = $item;
                    } elseif (is_object($item) && method_exists($item, 'getId')) {
                        $filedValues[] = $item->getId();
                    }
                }

                if ($field === 'deadline') {
                    foreach ($filedValues as $filedValue) {
                        $filterDates[] = [
                            'range' => [
                                $field => [
                                    'gte' => $filedValue . '||-1d',
                                    'lte' => $filedValue . '||+1d',
                                    'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE->value
                                ]
                            ],
                        ];
                    }
                } else {
                    $filter['must'][] = [
                        'nested' => [
                            'path' => $field,
                            'query' => [
                                'terms' => ["{$field}.id" => $filedValues]
                            ]
                        ]
                    ];
                }
            } elseif (is_string($value)) {
                $filter['must'][] = [
                    'match' => [$field => $value]
                ];
            } else {
                $filter['must'][] = [
                    'term' => [$field => $value]
                ];
            }
        }

        if (!empty($filter)) {
            $query['query']['bool']['must'][]['bool'] = $filter;
        }

        if (!empty($filterDates)) {
            $query['query']['bool']['filter']['bool']['should'] = $filterDates;
        }

        return $query;
    }
}
