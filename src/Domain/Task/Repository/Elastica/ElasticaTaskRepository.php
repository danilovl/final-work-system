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
use Webmozart\Assert\Assert;

class ElasticaTaskRepository
{
    public function __construct(private readonly TaskSearch $taskSearch) {}

    /**
     * @return int[]
     */
    public function getIdsByOwnerAndSearch(User $user, string $search): array
    {
        $result = $this->taskSearch->getIdsByOwnerAndSearch($user, $search);

        Assert::allInteger($result);

        return $result;
    }
}
