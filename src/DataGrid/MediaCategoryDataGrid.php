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

namespace App\DataGrid;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManager
};
use App\Entity\{
    User,
    MediaCategory
};
use App\Repository\MediaCategoryRepository;

class MediaCategoryDataGrid
{
    private MediaCategoryRepository $mediaCategoryRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->mediaCategoryRepository = $entityManager->getRepository(MediaCategory::class);
    }

    public function queryBuilderFindAllByOwner(User $user): QueryBuilder
    {
        return $this->mediaCategoryRepository->allByOwner($user);
    }
}
