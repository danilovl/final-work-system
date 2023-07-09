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

namespace App\Domain\Task\Elastica;

use App\Domain\User\Entity\User;
use Elastica\Result;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class TaskSearch
{
    public function __construct(private readonly TransformedFinder $transformedFinderTask) {}

    public function getIdsByOwnerAndSearch(User $user, string $search): array
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

        $results = $this->transformedFinderTask->findRaw($query);

        return array_map(static fn(Result $document): int => (int) $document->getId(), $results);
    }
}
