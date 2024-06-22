<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Command;

use App\Application\Service\S3ClientService;
use App\Domain\Media\Facade\MediaTypeFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:s3-create-buckets', description: 'Create s3 buckets.')]
class S3CreateBucketCommand extends Command
{
    public function __construct(
        private readonly MediaTypeFacade $mediaTypeFacade,
        private readonly S3ClientService $s3ClientService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mediaTypes = $this->mediaTypeFacade->findAll();

        foreach ($mediaTypes as $mediaType) {
            $folder = $mediaType->getFolder();

            $doesBucketExist = $this->s3ClientService->doesBucketExist($folder);
            if ($doesBucketExist) {
                continue;
            }

            $this->s3ClientService->createBucket($folder);
        }

        return Command::SUCCESS;
    }
}
