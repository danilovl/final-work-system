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

use App\Application\Service\EntityManagerService;
use App\Domain\Media\Facade\{
    MediaFacade,
    MediaTypeFacade
};
use App\Domain\MediaType\Entity\MediaType;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class SyncDatabaseMediaWithRealFileCommand extends Command
{
    final public const COMMAND_NAME = 'app:sync-database-media-with-real-file';
    private const LIMIT = 500;

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly MediaFacade $mediaFacade,
        private readonly MediaTypeFacade $mediaTypeFacade,
        private readonly ParameterServiceInterface $parameterService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Sync database media with real file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

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
            $medias = $this->mediaFacade->findAll($offset, static::LIMIT);
            if (count($medias) === 0) {
                break;
            }

            foreach ($medias as $media) {
                if (file_exists($media->getAbsolutePath())) {
                    continue;
                }

                $this->entityManagerService->remove($media);
                $count++;
            }

            $offset += static::LIMIT;
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
            foreach (new DirectoryIterator($folder) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                $media = $this->mediaFacade->findByMediaName($fileInfo->getFilename());
                if ($media !== null) {
                    continue;
                }

                unlink($fileInfo->getRealPath());
                $count++;
            }
        }

        $this->io->success(sprintf('%d files were deleted', $count));
    }

    private function syncMediaTypeFolder(OutputInterface $output): void
    {
        $uploadFolder = $this->parameterService->getString('upload_directory');
        $finder = new Finder;
        $finder->directories()->in($uploadFolder);

        $mediaTypes = $this->mediaTypeFacade->findAll();
        $mediaTypeFolders = array_map(fn(MediaType $mediaType): string => $mediaType->getFolder(), $mediaTypes);

        $progressBar = new ProgressBar($output, $finder->count());

        $removeFolders = [];
        foreach ($finder as $folder) {
            if (!in_array($folder->getBasename(), $mediaTypeFolders, true)) {
                $realPath = $folder->getRealPath();
                array_map('unlink', glob("$realPath/*.*"));
                $removeFolders[] = $realPath;
            }

            $progressBar->advance();
        }

        array_map('rmdir', $removeFolders);

        $this->io->success('Sync upload folder with media type folder is done');
    }
}
