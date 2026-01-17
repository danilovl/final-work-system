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

namespace App\Domain\Version\Bus\Command\DeleteVersion;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Infrastructure\Service\EntityManagerService;

readonly class DeleteVersionHandler implements CommandHandlerInterface
{
    public function __construct(private EntityManagerService $entityManagerService) {}

    public function __invoke(DeleteVersionCommand $command): void
    {
        $this->entityManagerService->remove($command->media);
    }
}
