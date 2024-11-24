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

namespace Application\Service;

use App\Application\Service\S3ClientService;
use App\Tests\Helper\Application\Service\S3Client;
use Aws\Command;
use Aws\S3\Exception\S3Exception;
use PHPUnit\Framework\TestCase;

class S3ClientServiceTest extends TestCase
{
    private S3Client $s3Client;
    private S3ClientService $s3ClientService;

    protected function setUp(): void
    {
        $this->s3Client = $this->createMock(S3Client::class);

        $this->s3ClientService = new S3ClientService($this->s3Client);
    }

    public function testCreateBucket(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('createBucket');

        $this->s3ClientService->createBucket('test');
    }

    public function testGetObject(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('getObject');

        $this->s3ClientService->getObject('test', 'test');

        $this->s3Client
            ->expects($this->never())
            ->method('getObject');

        $this->s3ClientService->getObject('test', 'test');
    }

    public function testGetObjectS3Exception(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('getObject')
            ->willThrowException(new S3Exception('message', new Command('name')));

        $result = $this->s3ClientService->getObject('test', 'test');

        $this->assertNull($result);
    }

    public function testDoesBucketExist(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('doesBucketExist')
            ->willReturn(true);

        $this->s3ClientService->doesBucketExist('test');
    }

    public function testDoesObjectExist(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(true);

        $this->s3ClientService->doesObjectExist('test', 'test');
    }

    public function testPutObject(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('putObject');

        $this->s3ClientService->putObject('test', 'test', 'test');
    }

    public function testDeleteObject(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('deleteObject');

        $this->s3ClientService->deleteObject('test', 'test');
    }
}
