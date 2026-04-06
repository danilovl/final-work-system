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

namespace App\Domain\Media\Command;

use App\Infrastructure\Service\S3ClientService;
use App\Domain\Media\Facade\MediaTypeFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:s3-create-buckets', description: 'Create s3 buckets.')]
class S3CreateBucketCommand
{
    final public const string COMMAND_NAME = 'app:s3-create-buckets';

    public function __construct(
        private readonly MediaTypeFacade $mediaTypeFacade,
        private readonly S3ClientService $s3ClientService
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $mediaTypes = $this->mediaTypeFacade->list();

        $io->title('Creating S3 buckets');

        foreach ($mediaTypes as $mediaType) {
            $folder = $mediaType->getFolder();

            $doesBucketExist = $this->s3ClientService->doesBucketExist($folder);
            if ($doesBucketExist) {
                $io->info(sprintf('Bucket "%s" already exists, skipping', $folder));

                continue;
            }

            $this->s3ClientService->createBucket($folder);
            $io->success(sprintf('Created bucket "%s"', $folder));
        }

        return Command::SUCCESS;
    }
}
