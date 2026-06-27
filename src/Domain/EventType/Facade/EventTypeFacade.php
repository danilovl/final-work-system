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

namespace App\Domain\EventType\Facade;

use App\Domain\EventType\Entity\EventType;
use App\Infrastructure\Service\EntityManagerService;

readonly class EventTypeFacade
{
    public function __construct(private EntityManagerService $entityManagerService) {}

    public function findById(int $id): ?EventType
    {
        /** @var EventType|null $result */
        $result = $this->entityManagerService
            ->getRepository(EventType::class)
            ->find($id);

        return $result;
    }

    /**
     * @return EventType[]
     */
    public function findAll(): array
    {
        /** @var EventType[] $result */
        $result = $this->entityManagerService
            ->getRepository(EventType::class)
            ->findAll();

        return $result;
    }
}
