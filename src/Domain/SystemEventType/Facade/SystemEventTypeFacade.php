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

namespace App\Domain\SystemEventType\Facade;

use App\Infrastructure\Exception\EntityNotFoundException;
use App\Domain\SystemEventType\Entity\SystemEventType;
use App\Infrastructure\Service\EntityManagerService;

readonly class SystemEventTypeFacade
{
    public function __construct(private EntityManagerService $entityManagerService) {}

    public function getById(int $id): SystemEventType
    {
        /** @var SystemEventType|null $systemEventType */
        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find($id);

        if ($systemEventType === null) {
            $message = sprintf('System event type "%d" not found.', $id);

            throw new EntityNotFoundException($message);
        }

        return $systemEventType;
    }
}
