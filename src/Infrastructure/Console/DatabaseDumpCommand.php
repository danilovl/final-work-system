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
use DateTimeImmutable;
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

    /** @var array{dbname: string, user: string, password: string} */
    private array $dbParams;

    private bool $isActive;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterServiceInterface $parameterService
    ) {
        $params = $this->entityManager->getConnection()->getParams();
        $this->dbParams = [
            'dbname' => $params['dbname'] ?? '',
            'user' => $params['user'] ?? '',
            'password' => $params['password'] ?? ''
        ];
        $this->isActive = $this->parameterService->getBoolean('database_dump.active');
    }

    public function __invoke(SymfonyStyle $io, OutputInterface $output): int
    {
        if (!$this->isActive) {
            return Command::SUCCESS;
        }

        $this->path = $this->getSaveFileName();

        $text = sprintf(
            'Dumping %s to %s',
            $this->dbParams['dbname'],
            $this->path
        );
        $io->text($text);

        $start = microtime(true);

        $progressBar = new ProgressBar(
            output: $output,
            max: 2
        );
        $progressBar->start();

        $this->createDirectoryIfRequired();
        $progressBar->advance();

        $this->dumpDatabaseUbuntu();
        $progressBar->advance();
        $progressBar->finish();

        $end = microtime(true);
        $time = number_format(($end - $start), 2);

        $io->newLine();
        $message = sprintf('Done in %s seconds', $time);
        $io->success($message);

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
        $mysqldump = $this->parameterService->getString('database_dump.path_to_mysqldump_exe');
        $user = escapeshellarg($this->dbParams['user']);
        $password = escapeshellarg($this->dbParams['password']);
        $database = escapeshellarg($this->dbParams['dbname']);
        $file = escapeshellarg($this->path);

        $cmd = sprintf(
            '%s -u %s -p%s %s > %s',
            $mysqldump,
            $user,
            $password,
            $database,
            $file
        );

        shell_exec($cmd);
    }

    private function getSaveFileName(): string
    {
        $folder = $this->parameterService->getString('database_dump.path_to_save_folder');
        $filePattern = $this->parameterService->getString('database_dump.file_pattern');
        $date = new DateTimeImmutable()->format('d.m.Y');

        return $folder . '/' . sprintf($filePattern, $date);
    }
}
