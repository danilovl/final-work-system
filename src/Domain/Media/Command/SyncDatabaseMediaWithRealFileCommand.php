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

namespace App\Domain\Media\Command;

use App\Application\Helper\FileHelper;
use App\Application\Service\{
    S3ClientService,
    EntityManagerService
};
use App\Domain\Media\Facade\{
    MediaFacade,
    MediaTypeFacade
};
use App\Domain\MediaType\Entity\MediaType;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class SyncDatabaseMediaWithRealFileCommand extends Command
{
    final public const string COMMAND_NAME = 'app:sync-database-media-with-real-file';

    private const int LIMIT = 500;

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly MediaFacade $mediaFacade,
        private readonly MediaTypeFacade $mediaTypeFacade,
        private readonly ParameterServiceInterface $parameterService,
        private readonly S3ClientService $s3ClientService
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Sync database media with real file');
    }

    #[Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->syncMediaTypeFolder($output);
        $this->syncMediaFile();
        $this->syncMedia();

        return Command::SUCCESS;
    }

    private function syncMedia(): void
    {
        $offset = 0;
        $count = 0;
        while (true) {
            $medias = $this->mediaFacade->findAll($offset, self::LIMIT);
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

        $this->io->success(sprintf('%d media were deleted', $count));
    }

    private function syncMediaFile(): void
    {
        $uploadFolder = $this->parameterService->getString('upload_directory');
        $mediaTypes = $this->mediaTypeFacade->findAll();

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

        $this->io->success(sprintf('%d files were deleted', $count));
    }

    private function syncMediaTypeFolder(OutputInterface $output): void
    {
        $uploadFolder = $this->parameterService->getString('upload_directory');
        $finder = new Finder;
        $finder->directories()->in($uploadFolder)->depth(0);

        $mediaTypes = $this->mediaTypeFacade->findAll();
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

        $this->io->success('Sync upload folder with media type folder is done');
    }
}
