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

namespace App\Domain\WorkType\Facade;

use App\Application\Exception\EntityNotFoundException;
use App\Domain\WorkType\Entity\WorkType;
use App\Domain\WorkType\Repository\WorkTypeRepository;

readonly class WorkTypeFacade
{
    public function __construct(private WorkTypeRepository $workTypeRepository) {}

    public function findById(int $id): ?WorkType
    {
        /** @var WorkType|null $result */
        $result = $this->workTypeRepository->find($id);

        return $result;
    }

    public function getById(int $id): WorkType
    {
        $workType = $this->findById($id);
        if ($workType === null) {
            throw new EntityNotFoundException("WorkType with id {$id} not found.");
        }

        return $workType;
    }

    /**
     * @return WorkType[]
     */
    public function getAll(): array
    {
        /** @var WorkType[] $result */
        $result = $this->workTypeRepository->findAll();

        return $result;
    }
}
