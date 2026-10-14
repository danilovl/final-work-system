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

namespace App\Domain\MediaCategory\Facade;

use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\Repository\MediaCategoryRepository;
use Doctrine\ORM\Query;
use App\Domain\User\Entity\User;

readonly class MediaCategoryFacade
{
    public function __construct(private MediaCategoryRepository $mediaCategoryRepository) {}

    public function queryByOwner(User $user): Query
    {
        return $this->mediaCategoryRepository
            ->allByOwner($user)
            ->getQuery();
    }

    /**
     * @return MediaCategory[]
     */
    public function listByOwner(User $user): array
    {
        /** @var MediaCategory[] $result */
        $result = $this->mediaCategoryRepository
            ->allByOwner($user)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return MediaCategory[]
     */
    public function listByOwners(iterable $users): array
    {
        /** @var MediaCategory[] $result */
        $result = $this->mediaCategoryRepository
            ->allByOwners($users)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
