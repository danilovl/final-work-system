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

namespace App\Infrastructure\Service;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Service\ResetInterface;

class S3ClientService implements ResetInterface
{
    private const int EXIST_CACHE_TTL = 3_600;

    /**
     * @var array<string, bool>
     */
    private array $existCache = [];

    public function __construct(
        private readonly S3Client $s3Client,
        private readonly CacheItemPoolInterface $appCache
    ) {}

    public function createBucket(string $bucket): void
    {
        $this->s3Client->createBucket([
            'Bucket' => $bucket
        ]);
    }

    public function getObject(string $bucket, string $key, ?string $saveAs = null): ?Result
    {
        $args = [
            'Bucket' => $bucket,
            'Key' => $key
        ];

        if ($saveAs !== null) {
            $args['SaveAs'] = $saveAs;
        }

        try {
            return $this->s3Client->getObject($args);
        } catch (S3Exception) {
            return null;
        }
    }

    public function doesBucketExist(string $bucket): bool
    {
        return $this->s3Client->doesBucketExist($bucket);
    }

    public function doesObjectExist(string $bucket, string $key, bool $useCache = true): bool
    {
        if (!$useCache) {
            return $this->s3Client->doesObjectExist($bucket, $key);
        }

        $cacheKey = $this->getCacheKey($bucket, $key);
        if (isset($this->existCache[$cacheKey])) {
            return $this->existCache[$cacheKey];
        }

        $cacheItem = $this->appCache->getItem($this->getExistCacheKey($bucket, $key));
        if ($cacheItem->isHit()) {
            $isExist = (bool) $cacheItem->get();
        } else {
            $isExist = $this->s3Client->doesObjectExist($bucket, $key);

            $cacheItem->set($isExist);
            $cacheItem->expiresAfter(self::EXIST_CACHE_TTL);
            $this->appCache->save($cacheItem);
        }

        $this->existCache[$cacheKey] = $isExist;

        return $isExist;
    }

    public function putObject(string $bucket, string $key, string $filePath): void
    {
        $this->s3Client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $filePath
        ]);

        $this->clearCacheKey($bucket, $key);
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

    private function getExistCacheKey(string $bucket, string $key): string
    {
        return 's3_object_exist_' . hash('xxh128', $this->getCacheKey($bucket, $key));
    }

    private function clearCacheKey(string $bucket, string $key): void
    {
        $cacheKey = $this->getCacheKey($bucket, $key);

        unset($this->existCache[$cacheKey]);
        $this->appCache->deleteItem($this->getExistCacheKey($bucket, $key));
    }

    public function reset(): void
    {
        $this->existCache = [];
    }
}
