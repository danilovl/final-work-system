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

use App\Service\EntityManagerService;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;

class ImportSqlCommand extends Command
{
    protected static $defaultName = 'app:import-sql';

    public function __construct(private EntityManagerService $entityManagerService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Import SQL file(s) directly to Database.')
            ->addArgument('file', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'File path(s) of SQL to be executed.');
    }

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

            $output->write(sprintf("Processing file '<info>%s</info>' ", $filePath));
            $sql = file_get_contents($filePath);

            try {
                $stmt = $connection->prepare($sql);
                $stmt->executeStatement();

                $output->write(sprintf('%d file was executed!', $filePath) . PHP_EOL);
            } catch (PDOException $e) {
                $output->write('error!' . PHP_EOL);

                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
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