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

use App\Application\Traits\Repository\Elastica\ElasticaSearchTrait;
use App\Domain\User\Entity\User;
use FOS\ElasticaBundle\Finder\TransformedFinder;

readonly class TaskSearch
{
    use ElasticaSearchTrait;

    public function __construct(private TransformedFinder $transformedFinderTask) {}

    /**
     * @return int[]
     */
    public function getIdsByOwnerAndSearch(User $user, string $search): array
    {
        $query = $this->createQuery($user, $search);
        $results = $this->transformedFinderTask->findRaw($query);

        return $this->getDocumentIds($results);
    }

    /**
     * @return array{
     *     size: int,
     *     _source: false,
     *     query: array
     * }
     */
    public function createQuery(User $user, string $search): array
    {
        return [
            'size' => 1_000,
            '_source' => false,
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['owner.id' => $user->getId()]],
                        [
                            'bool' => [
                                'should' => [
                                    $this->createContainsQuery('name.normalized', $search),
                                    $this->createContainsQuery('description', $search)
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
