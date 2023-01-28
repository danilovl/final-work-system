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

namespace App\Domain\WorkCategory\Facade;

use App\Domain\WorkCategory\Repository\WorkCategoryRepository;
use Doctrine\ORM\Query;
use App\Domain\User\Entity\User;

readonly class WorkCategoryFacade
{
    public function __construct(private WorkCategoryRepository $workCategoryRepository) {}

    public function queryWorkCategoriesByOwner(User $user): Query
    {
        return $this->workCategoryRepository
            ->allByOwner($user)
            ->getQuery();
    }
}
