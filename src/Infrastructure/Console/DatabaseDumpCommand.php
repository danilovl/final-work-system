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

namespace App\Infrastructure\Console;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:database:dump')]
class DatabaseDumpCommand
{
    public const string COMMAND_NAME = 'app:database:dump';

    private string $path;

    private array $dbParams;

    private bool $isActive;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterServiceInterface $parameterService
    ) {
        $this->dbParams = $this->entityManager->getConnection()->getParams();
        $this->isActive = $this->parameterService->get('database_dump.active');
    }

    public function __invoke(SymfonyStyle $io, OutputInterface $output): int
    {
        if (!$this->isActive) {
            return Command::SUCCESS;
        }

        $this->path = $this->getSaveFileName();

        $text = sprintf('Dumping %s to %s',
            $this->dbParams['dbname'],
            $this->path
        );
        $io->text($text);

        $start = microtime(true);

        $progressBar = new ProgressBar($output, 2);
        $progressBar->start();

        $this->createDirectoryIfRequired();
        $progressBar->advance();

        $this->dumpDatabaseUbuntu();
        $progressBar->advance();
        $progressBar->finish();

        $end = microtime(true);
        $time = number_format(($end - $start), 2);

        $io->newLine();
        $io->success(sprintf('Done in %s seconds', $time));

        return Command::SUCCESS;
    }

    private function createDirectoryIfRequired(): void
    {
        $fs = new Filesystem;
        if (!$fs->exists($this->path)) {
            $fs->mkdir(dirname($this->path));
        }
    }

    private function dumpDatabaseUbuntu(): void
    {
        $cmd = sprintf('%s -u %s -p%s %s > %s',
            $this->parameterService->get('database_dump.path_to_mysqldump_exe'),
            $this->dbParams['user'],
            $this->dbParams['password'],
            $this->dbParams['dbname'],
            $this->getSaveFileName()
        );

        shell_exec($cmd);
    }

    private function getSaveFileName(): string
    {
        return sprintf('%s/' . $this->parameterService->get('database_dump.file_pattern'),
            $this->parameterService->get('database_dump.path_to_save_folder'),
            (new DateTime)->format('d.m.Y')
        );
    }
}
