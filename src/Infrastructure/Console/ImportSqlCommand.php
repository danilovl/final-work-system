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

use App\Application\Exception\InvalidArgumentException;
use App\Application\Service\EntityManagerService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-sql', description: 'Import SQL file(s) directly to Database.')]
class ImportSqlCommand extends Command
{
    final public const string COMMAND_NAME = 'app:import-sql';

    public function __construct(private readonly EntityManagerService $entityManagerService)
    {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'File path(s) of SQL to be executed.');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManagerService->getConnection();
        $fileNames = (array) $input->getArgument('file');

        foreach ($fileNames as $fileName) {
            $filePath = realpath($fileName);
            if ($filePath === false) {
                $filePath = $fileName;
            }

            $this->validationFilePath($filePath);

            $output->writeln(sprintf("Processing file '<info>%s</info>' ", $filePath));
            $sql = file_get_contents($filePath);

            $stmt = $connection->prepare($sql);
            $stmt->executeStatement();

            $output->writeln(sprintf('%s file was executed!', $filePath) . PHP_EOL);
        }

        return Command::SUCCESS;
    }

    private function validationFilePath(string $filePath): void
    {
        $message = null;

        if (!file_exists($filePath)) {
            $message = sprintf("SQL file '<info>%s</info>' does not exist.", $filePath);
        } elseif (!is_readable($filePath)) {
            $message = sprintf("SQL file '<info>%s</info>' does not have read permissions.", $filePath);
        }

        if ($message === null) {
            return;
        }

        throw new InvalidArgumentException($message);
    }
}
