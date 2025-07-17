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
use Predis\ClientInterface;

class RedisCache extends CacheProvider
{
    private ClientInterface $redis;

    public function setRedis(ClientInterface $redis): void
    {
        $this->redis = $redis;
    }

    public function getRedis(): ClientInterface
    {
        return $this->redis;
    }

    #[Override]
    protected function doFetch($id): mixed
    {
        $value = $this->redis->get($id);

        return $value !== null ? unserialize($value) : false;
    }

    #[Override]
    protected function doFetchMultiple(array $keys): array
    {
        $values = $this->redis->mget($keys);
        $result = [];

        foreach ($keys as $index => $key) {
            $value = $values[$index] ?? null;
            if ($value !== null) {
                $result[$key] = unserialize($value);
            }
        }

        return $result;
    }

    /**
     * @param array<string, string> $keysAndValues
     */
    #[Override]
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0): bool
    {
        $pipeline = $this->redis->pipeline();

        foreach ($keysAndValues as $key => $value) {
            $serializedValue = serialize($value);
            if ($lifetime > 0) {
                $pipeline->setex($key, $lifetime, $serializedValue);
            } else {
                $pipeline->set($key, $serializedValue);
            }
        }

        $responses = $pipeline->execute();

        return count(array_filter($responses)) === count($keysAndValues);

    }

    #[Override]
    protected function doContains($id): bool
    {
        return $this->redis->exists($id) > 0;
    }

    #[Override]
    protected function doSave($id, $data, $lifeTime = 0): bool
    {
        $serializedData = serialize($data);

        if ($lifeTime > 0) {
            return $this->redis->setex($id, $lifeTime, $serializedData) === true;
        }

        return $this->redis->set($id, $serializedData) === true;
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
}
