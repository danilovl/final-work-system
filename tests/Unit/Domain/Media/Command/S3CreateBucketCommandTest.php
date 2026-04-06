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

use App\Domain\Media\Command\S3CreateBucketCommand;
use App\Domain\Media\Facade\MediaTypeFacade;
use App\Domain\MediaType\Entity\MediaType;
use App\Infrastructure\Service\S3ClientService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\TypeInfo\Exception\LogicException;

class S3CreateBucketCommandTest extends TestCase
{
    private MockObject&MediaTypeFacade $mediaTypeFacade;

    private MockObject&S3ClientService $s3ClientService;

    private S3CreateBucketCommand $s3CreateBucketCommand;

    protected function setUp(): void
    {
        $this->mediaTypeFacade = $this->createMock(MediaTypeFacade::class);
        $this->s3ClientService = $this->createMock(S3ClientService::class);
        $this->s3CreateBucketCommand = new S3CreateBucketCommand(
            $this->mediaTypeFacade,
            $this->s3ClientService
        );
    }

    public function testExecute(): void
    {
        $mediaType1 = $this->createMock(MediaType::class);
        $mediaType1->method('getFolder')->willReturn('testFolder1');

        $mediaType2 = $this->createMock(MediaType::class);
        $mediaType2->method('getFolder')->willReturn('testFolder2');

        $this->mediaTypeFacade
            ->method('list')
            ->willReturn([$mediaType1, $mediaType2]);

        $this->s3ClientService
            ->expects($this->exactly(2))
            ->method('doesBucketExist')
            ->willReturnCallback(static function (string $param): bool {
                return match ($param) {
                    'testFolder1' => false,
                    'testFolder2' => true,
                    default => throw new LogicException('Unexpected bucket'),
                };
            });

        $this->s3ClientService
            ->expects($this->once())
            ->method('createBucket')
            ->with('testFolder1');

        $input = new ArrayInput([]);
        $output = new BufferedOutput;
        $io = new SymfonyStyle($input, $output);

        $status = ($this->s3CreateBucketCommand)($io);

        $this->assertSame(Command::SUCCESS, $status);
    }
}
