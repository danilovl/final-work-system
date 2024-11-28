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

class ImportSqlCommandTest extends KernelTestCase
{
    private string $sqlFilePath;

    protected function setUp(): void
    {
        $this->sqlFilePath = $this->createSqlFile();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->sqlFilePath)) {
            unlink($this->sqlFilePath);
        }
    }

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(ImportSqlCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => $this->sqlFilePath
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

        $this->expectException(InvalidArgumentException::class);

        chmod($this->sqlFilePath, 0000);

        $command = $application->find(ImportSqlCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => $this->sqlFilePath
        ]);
    }

    private function createSqlFile(): string
    {
        /** @var string $filePath */
        $filePath = tempnam(sys_get_temp_dir(), 'import-sql-command');
        file_put_contents($filePath, $this->getSql());

        return $filePath;
    }

    private function getSql(): string
    {
        return "
            CREATE TABLE IF NOT EXISTS `import_sql_command_test`
            (
                `id`        INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `firstname` VARCHAR(30) NOT NULL,
                `lastname`  VARCHAR(30) NOT NULL,
                `email`     VARCHAR(50)
            );
            
            INSERT INTO `import_sql_command_test` (`firstname`, `lastname`, `email`)
            VALUES ('firstname', 'lastname', 'firstname@email.com');
            
            DROP TABLE `import_sql_command_test`;
        ";
    }
}
