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

namespace App\Command;

use App\Entity\MediaType;
use App\Model\Media\{
    MediaFacade,
    MediaTypeFacade
};
use App\Services\EntityManagerService;
use Danilovl\ParameterBundle\Services\ParameterService;
use DirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class SyncDatabaseMediaWithRealFileCommand extends Command
{
    protected static $defaultName = 'app:sync-database-media-with-real-file';
    private const LIMIT = 500;

    private SymfonyStyle $io;
    private EntityManagerService $entityManagerService;
    private ParameterService $parameterService;
    private MediaFacade $mediaFacade;
    private MediaTypeFacade $mediaTypeFacade;

    public function __construct(
        EntityManagerService $entityManagerService,
        MediaFacade $mediaFacade,
        MediaTypeFacade $mediaTypeFacade,
        ParameterService $parameterService
    ) {
        parent::__construct();

        $this->parameterService = $parameterService;
        $this->mediaFacade = $mediaFacade;
        $this->mediaTypeFacade = $mediaTypeFacade;
        $this->entityManagerService = $entityManagerService;
    }

    protected function configure()
    {
        $this->setDescription('Sync-database-media-with-real-file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
        $uploadFolder = $this->parameterService->get('upload_directory');

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
        $uploadFolder = $this->parameterService->get('upload_directory');
        $finder = new Finder();
        $finder->directories()->in($uploadFolder);

        $mediaTypes = $this->mediaTypeFacade->findAll();
        $mediaTypeFolders = array_map(function (MediaType $mediaType): string {
            return $mediaType->getFolder();
        }, $mediaTypes);

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
