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

namespace App\Domain\MediaCategory\Form\DataGrid;

use App\Domain\MediaCategory\Repository\MediaCategoryRepository;
use Doctrine\ORM\QueryBuilder;
use App\Domain\User\Entity\User;

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
