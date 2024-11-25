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

namespace App\Tests\Unit\Domain\Media\Command;

use App\Application\Service\S3ClientService;
use App\Domain\Media\Facade\MediaTypeFacade;
use App\Domain\MediaType\Entity\MediaType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class S3CreateBucketCommandTest extends TestCase
{
    private MediaTypeFacade $mediaTypeFacade;

    private S3ClientService $s3ClientService;

    private \App\Domain\Media\Command\S3CreateBucketCommand $s3CreateBucketCommand;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->mediaTypeFacade = $this->createMock(MediaTypeFacade::class);
        $this->s3ClientService = $this->createMock(S3ClientService::class);
        $this->s3CreateBucketCommand = new \App\Domain\Media\Command\S3CreateBucketCommand($this->mediaTypeFacade, $this->s3ClientService);

        $this->commandTester = new CommandTester($this->s3CreateBucketCommand);
    }

    public function testExecute(): void
    {
        $mediaType1 = $this->createMock(MediaType::class);
        $mediaType1->method('getFolder')->willReturn('testFolder1');

        $mediaType2 = $this->createMock(MediaType::class);
        $mediaType2->method('getFolder')->willReturn('testFolder2');

        $this->mediaTypeFacade
            ->method('findAll')
            ->willReturn([$mediaType1, $mediaType2]);

        $this->s3ClientService
            ->expects($this->exactly(2))
            ->method('doesBucketExist')
            ->willReturnCallback(static function (string $param): bool {
                return match ($param) {
                    'testFolder1' => false,
                    'testFolder2' => true
                };
            });

        $this->s3ClientService
            ->expects($this->once())
            ->method('createBucket')
            ->with('testFolder1');

        $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}
