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

namespace App\Application\Redis\Provider;

use Doctrine\Common\Cache\{
    Cache,
    CacheProvider
};
use Override;
use Redis;

class RedisCache extends CacheProvider
{
    private Redis $redis;

    public function setRedis(Redis $redis): void
    {
        $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->redis = $redis;
    }

    public function getRedis(): Redis
    {
        return $this->redis;
    }

    #[Override]
    protected function doFetch($id): mixed
    {
        return $this->redis->get($id);
    }

    #[Override]
    protected function doFetchMultiple(array $keys): array
    {
        $fetchedItems = array_combine($keys, $this->redis->mget($keys));

        $keysToFilter = array_keys(array_filter($fetchedItems, static fn ($item): bool => $item === false));

        if ($keysToFilter) {
            $multi = $this->redis->multi(Redis::PIPELINE);
            foreach ($keysToFilter as $key) {
                $multi->exists($key);
            }

            $existItems = array_filter($multi->exec());
            $missedItemKeys = array_diff_key($keysToFilter, $existItems);
            $fetchedItems = array_diff_key($fetchedItems, array_fill_keys($missedItemKeys, true));
        }

        return $fetchedItems;
    }

    /**
     * @param array<string, string> $keysAndValues
     */
    #[Override]
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0): bool
    {
        if ($lifetime) {
            $multi = $this->redis->multi(Redis::PIPELINE);
            foreach ($keysAndValues as $key => $value) {
                $multi->setex($key, $lifetime, $value);
            }

            $succeeded = array_filter($multi->exec());

            return count($succeeded) === count($keysAndValues);
        }

        return $this->redis->mset($keysAndValues);
    }

    #[Override]
    protected function doContains($id): bool
    {
        /** @var int|bool $exists */
        $exists = $this->redis->exists($id);

        if (is_int($exists)) {
            return $exists > 0;
        }

        return $exists;
    }

    #[Override]
    protected function doSave($id, $data, $lifeTime = 0): bool
    {
        if ($lifeTime > 0) {
            return $this->redis->setex($id, $lifeTime, $data);
        }

        return $this->redis->set($id, $data);
    }

    #[Override]
    protected function doDelete($id): bool
    {
        return $this->redis->del($id) >= 0;
    }

    #[Override]
    protected function doDeleteMultiple(array $keys): bool
    {
        return $this->redis->del($keys) >= 0;
    }

    #[Override]
    protected function doFlush(): bool
    {
        return $this->redis->flushDB();
    }

    #[Override]
    protected function doGetStats(): array
    {
        $info = $this->redis->info();

        return [
            Cache::STATS_HITS => $info['keyspace_hits'],
            Cache::STATS_MISSES => $info['keyspace_misses'],
            Cache::STATS_UPTIME => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE => false
        ];
    }

    protected function getSerializerValue(): int
    {
        if (defined('Redis::SERIALIZER_IGBINARY') && extension_loaded('igbinary')) {
            return Redis::SERIALIZER_IGBINARY;
        }

        return Redis::SERIALIZER_PHP;
    }
}
