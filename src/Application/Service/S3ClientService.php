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

namespace App\Application\Service;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3ClientService
{
    /**
     * @var Result[]
     */
    private array $cache = [];

    public function __construct(private readonly S3Client $s3Client) {}

    public function createBucket(string $bucket): void
    {
        $this->s3Client->createBucket([
            'Bucket' => $bucket
        ]);
    }

    public function getObject(string $bucket, string $key): ?Result
    {
        $result = $this->getFromCache($bucket, $key);
        if ($result) {
            return $result;
        }

        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);

            $this->saveToCache($result, $bucket, $key);

            return $result;
        } catch (S3Exception) {
            return null;
        }
    }

    public function doesBucketExist(string $bucket): bool
    {
        return $this->s3Client->doesBucketExist($bucket);
    }

    public function doesObjectExist(string $bucket, string $key): bool
    {
        return $this->s3Client->doesObjectExist($bucket, $key);
    }

    public function putObject(string $bucket, string $key, string $filePath): void
    {
        $this->s3Client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $filePath
        ]);
    }

    public function deleteObject(string $bucket, string $key): void
    {
        $this->s3Client->deleteObject([
            'Bucket' => $bucket,
            'Key' => $key
        ]);

        $this->clearCacheKey($bucket, $key);
    }

    private function getCacheKey(string $bucket, string $key): string
    {
        return sprintf('%s:%s', $bucket, $key);
    }

    private function getFromCache(string $bucket, string $key): ?Result
    {
        return $this->cache[$this->getCacheKey($bucket, $key)] ?? null;
    }

    private function saveToCache(Result $result, string $bucket, string $key): void
    {
        $this->cache[$this->getCacheKey($bucket, $key)] = $result;
    }

    private function clearCacheKey(string $bucket, string $key): void
    {
        unset($this->cache[$this->getCacheKey($bucket, $key)]);
    }
}
