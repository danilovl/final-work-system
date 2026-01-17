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

namespace App\Domain\Work\Bus\Command\DeleteWork;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Infrastructure\Service\{
    S3ClientService,
    EntityManagerService
};

readonly class DeleteWorkHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private S3ClientService $s3ClientService
    ) {}

    public function __invoke(DeleteWorkCommand $command): void
    {
        $work = $command->work;

        $workMedia = $work->getMedias();
        foreach ($workMedia as $media) {
            $this->s3ClientService->deleteObject(
                $media->getType()->getFolder(),
                $media->getMediaName()
            );
        }

        $this->entityManagerService->remove($work);
    }
}
