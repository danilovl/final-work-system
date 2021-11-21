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

namespace App\Model\MediaCategory\Facade;

use App\Model\MediaCategory\Repository\MediaCategoryRepository;
use Doctrine\ORM\Query;
use App\Model\User\Entity\User;

class MediaCategoryFacade
{
    public function __construct(private MediaCategoryRepository $mediaCategoryRepository)
    {
    }

    public function queryMediaCategoriesByOwner(User $user): Query
    {
        return $this->mediaCategoryRepository
            ->allByOwner($user)
            ->getQuery();
    }

    public function getMediaCategoriesByOwner(User $user): array
    {
        return $this->mediaCategoryRepository
            ->allByOwner($user)
            ->getQuery()
            ->getResult();
    }

    public function getMediaCategoriesByOwners(iterable $users): array
    {
        return $this->mediaCategoryRepository
            ->allByOwners($users)
            ->getQuery()
            ->getResult();
    }
}
