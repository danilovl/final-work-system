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

namespace App\Tests\Kernel\Application\Command;

use App\Application\Command\ImportSqlCommand;
use App\Application\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Throwable;

class ImportSqlCommandTest extends KernelTestCase
{
    private const string SQL_FILE_PATH = __DIR__ . '/data/import-sql-command-data.sql';

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(ImportSqlCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => self::SQL_FILE_PATH
        ]);

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteNotExistFile(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $this->expectException(InvalidArgumentException::class);

        $command = $application->find(ImportSqlCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => 'not-exist-file'
        ]);
    }

    public function testExecuteNotReadable(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $this->expectException(Throwable::class);

        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'import-sql-command');
        chmod($temporaryFilePath, 0000);

        $command = $application->find(ImportSqlCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => $temporaryFilePath
        ]);
    }
}
