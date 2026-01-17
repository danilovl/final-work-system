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

namespace App\Domain\Event\Bus\Command\DeleteEvent;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Event\Entity\Event;

readonly class DeleteEventHandler implements CommandHandlerInterface
{
    public function __construct(private EntityManagerService $entityManagerService) {}

    public function __invoke(DeleteEventCommand $command): void
    {
        $id = $command->event->getId();
        $this->entityManagerService->removeNativeSql(Event::class, $id);
    }
}
