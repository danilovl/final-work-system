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

namespace App\Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\S3ClientService;
use App\Tests\Mock\Application\Service\S3ClientMock;
use Aws\Command;
use Aws\S3\Exception\S3Exception;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class S3ClientServiceTest extends TestCase
{
    private MockObject&S3ClientMock $s3Client;

    private S3ClientService $s3ClientService;

    private ArrayAdapter $appCache;

    protected function setUp(): void
    {
        $this->s3Client = $this->createMock(S3ClientMock::class);
        $this->appCache = new ArrayAdapter;

        $this->s3ClientService = new S3ClientService(
            s3Client: $this->s3Client,
            appCache: $this->appCache
        );
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

    public function testDoesObjectExistUsesRequestCache(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(true);

        $isExistFirst = $this->s3ClientService->doesObjectExist('test', 'test');
        $isExistSecond = $this->s3ClientService->doesObjectExist('test', 'test');

        $this->assertTrue($isExistFirst);
        $this->assertTrue($isExistSecond);
    }

    public function testDoesObjectExistUsesAppCacheBetweenRequests(): void
    {
        $this->s3Client
            ->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(false);

        $isExist = $this->s3ClientService->doesObjectExist('test', 'test');

        $this->assertFalse($isExist);

        $nextRequestService = new S3ClientService(
            s3Client: $this->s3Client,
            appCache: $this->appCache
        );

        $isExistNextRequest = $nextRequestService->doesObjectExist('test', 'test');

        $this->assertFalse($isExistNextRequest);
    }

    public function testDoesObjectExistWithoutCache(): void
    {
        $this->s3Client
            ->expects($this->exactly(2))
            ->method('doesObjectExist')
            ->willReturn(true);

        $this->s3ClientService->doesObjectExist('test', 'test');
        $this->s3ClientService->doesObjectExist(
            bucket: 'test',
            key: 'test',
            useCache: false
        );
    }

    #[DataProvider('provideDoesObjectExistCacheClearedCases')]
    public function testDoesObjectExistCacheCleared(string $method, array $arguments): void
    {
        $this->s3Client
            ->expects($this->exactly(2))
            ->method('doesObjectExist')
            ->willReturnOnConsecutiveCalls(false, true);

        $isExist = $this->s3ClientService->doesObjectExist('test', 'test');

        $this->assertFalse($isExist);

        $this->s3ClientService->{$method}(...$arguments);

        $nextRequestService = new S3ClientService(
            s3Client: $this->s3Client,
            appCache: $this->appCache
        );

        $isExistNextRequest = $nextRequestService->doesObjectExist('test', 'test');

        $this->assertTrue($isExistNextRequest);
    }

    public static function provideDoesObjectExistCacheClearedCases(): Generator
    {
        yield 'put object' => [
            'method' => 'putObject',
            'arguments' => ['test', 'test', 'test']
        ];

        yield 'delete object' => [
            'method' => 'deleteObject',
            'arguments' => ['test', 'test']
        ];
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
