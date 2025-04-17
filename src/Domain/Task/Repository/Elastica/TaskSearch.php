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

namespace App\Domain\Task\Repository\Elastica;

use App\Domain\User\Entity\User;
use Elastica\Result;
use FOS\ElasticaBundle\Finder\TransformedFinder;

readonly class TaskSearch
{
    public function __construct(private TransformedFinder $transformedFinderTask) {}

    /**
     * @return int[]
     */
    public function getIdsByOwnerAndSearch(User $user, string $search): array
    {
        $query = $this->createQuery($user, $search);
        $results = $this->transformedFinderTask->findRaw($query);

        return array_map(static fn (Result $document): int => (int) $document->getId(), $results);
    }

    /**
     * @return array{
     *     size: int,
     *     _source: array<string>,
     *     query: array
     * }
     */
    public function createQuery(User $user, string $search): array
    {
        $search = mb_strtolower($search);

        return [
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
        ];
    }
}
