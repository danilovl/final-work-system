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

namespace Application\Command;

use App\Application\Command\S3CreateBucketCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class S3CreateBucketCommandTest extends KernelTestCase
{
    private readonly S3CreateBucketCommand $command;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->command = $kernel->getContainer()->get(S3CreateBucketCommand::class);
    }

    public function testExecute(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}
