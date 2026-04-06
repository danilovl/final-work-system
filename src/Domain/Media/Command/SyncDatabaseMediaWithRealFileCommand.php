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

use App\Application\Helper\FileHelper;
use App\Infrastructure\Service\{
    S3ClientService,
    EntityManagerService
};
use App\Domain\Media\Facade\{
    MediaFacade,
    MediaTypeFacade
};
use App\Domain\MediaType\Entity\MediaType;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'app:sync-database-media-with-real-file', description: 'Sync database media with real file')]
class SyncDatabaseMediaWithRealFileCommand
{
    final public const string COMMAND_NAME = 'app:sync-database-media-with-real-file';

    private const int LIMIT = 500;

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly MediaFacade $mediaFacade,
        private readonly MediaTypeFacade $mediaTypeFacade,
        private readonly ParameterServiceInterface $parameterService,
        private readonly S3ClientService $s3ClientService
    ) {}

    public function __invoke(SymfonyStyle $io, OutputInterface $output): int
    {
        $this->syncMediaTypeFolder($output, $io);
        $this->syncMediaFile($io);
        $this->syncMedia($io);

        return Command::SUCCESS;
    }

    private function syncMedia(SymfonyStyle $io): void
    {
        $offset = 0;
        $count = 0;
        while (true) {
            $medias = $this->mediaFacade->list($offset, self::LIMIT);
            if (count($medias) === 0) {
                break;
            }

            foreach ($medias as $media) {
                $isExist = $this->s3ClientService->doesObjectExist(
                    $media->getType()->getFolder(),
                    $media->getMediaName()
                );

                if ($isExist) {
                    continue;
                }

                $this->entityManagerService->remove($media);
                $count++;
            }

            $offset += self::LIMIT;
        }

        $io->success(sprintf('%d media were deleted', $count));
    }

    private function syncMediaFile(SymfonyStyle $io): void
    {
        $uploadFolder = $this->parameterService->getString('upload_directory');
        $mediaTypes = $this->mediaTypeFacade->list();

        $count = 0;
        foreach ($mediaTypes as $mediaType) {
            $folder = $uploadFolder . $mediaType->getFolder();

            $finder = new Finder;
            $finder->directories()->in($folder)->depth(0);

            foreach ($finder as $folder) {
                $media = $this->mediaFacade->findByMediaName($folder->getBasename());
                if ($media !== null) {
                    continue;
                }

                FileHelper::deleteDirectory($folder->getRealPath());

                $count++;
            }
        }

        $io->success(sprintf('%d files were deleted', $count));
    }

    private function syncMediaTypeFolder(OutputInterface $output, SymfonyStyle $io): void
    {
        $uploadFolder = $this->parameterService->getString('upload_directory');
        $finder = new Finder;
        $finder->directories()->in($uploadFolder)->depth(0);

        $mediaTypes = $this->mediaTypeFacade->list();
        $mediaTypeFolders = array_map(static fn (MediaType $mediaType): string => $mediaType->getFolder(), $mediaTypes);

        $progressBar = new ProgressBar($output, $finder->count());

        $removeFolders = [];
        foreach ($finder as $folder) {
            if (!in_array($folder->getBasename(), $mediaTypeFolders, true)) {
                $realPath = $folder->getRealPath();
                $removeFolders[] = $realPath;
            }

            $progressBar->advance();
        }

        foreach ($removeFolders as $removeFolder) {
            FileHelper::deleteDirectory($removeFolder);
        }

        $io->success('Sync upload folder with media type folder is done');
    }
}
