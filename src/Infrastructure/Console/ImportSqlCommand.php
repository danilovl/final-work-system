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
use App\Infrastructure\Service\EntityManagerService;
use Symfony\Component\Console\Attribute\{
    Argument,
    AsCommand
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-sql', description: 'Import SQL file(s) directly to Database.')]
class ImportSqlCommand
{
    final public const string COMMAND_NAME = 'app:import-sql';

    public function __construct(private readonly EntityManagerService $entityManagerService) {}

    public function __invoke(
        #[Argument(
            description: 'File path(s) of SQL to be executed.',
            name: 'file'
        )]
        array $file,
        OutputInterface $output
    ): int {
        $connection = $this->entityManagerService->getConnection();

        foreach ($file as $fileName) {
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
