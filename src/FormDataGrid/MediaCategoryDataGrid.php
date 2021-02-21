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

namespace App\FormDataGrid;

use Doctrine\ORM\QueryBuilder;
use App\Entity\User;
use App\Repository\MediaCategoryRepository;

class MediaCategoryDataGrid
{
    public function __construct(private MediaCategoryRepository $mediaCategoryRepository)
    {
    }

    public function queryBuilderFindAllByOwner(User $user): QueryBuilder
    {
        return $this->mediaCategoryRepository->allByOwner($user);
    }
}
