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

namespace App\Infrastructure\Session;

use SessionHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RedisLockingSessionHandler extends SessionHandler
{
    private const int LOCK_EXPIRE_SECONDS = 60;

    private const int LOCK_WAIT_MICROSECONDS = 20_000;

    private const int LOCK_RETRIES = 500;

    private const int DATABASE = 12;

    public function __construct(
        #[Autowire(env: 'REDIS_HOSTNAME')]
        string $host,
        #[Autowire(env: 'int:REDIS_PORT')]
        int $port
    ) {
        $savePath = sprintf('tcp://%s:%d?database=%d', $host, $port, self::DATABASE);

        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $savePath);
        ini_set('redis.session.locking_enabled', '1');
        ini_set('redis.session.lock_expire', (string) self::LOCK_EXPIRE_SECONDS);
        ini_set('redis.session.lock_wait_time', (string) self::LOCK_WAIT_MICROSECONDS);
        ini_set('redis.session.lock_retries', (string) self::LOCK_RETRIES);
        ini_set('redis.session.lock_failure_readonly', '1');
    }
}
