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

namespace App\Tests\Unit\Domain\SystemEvent\EventSubscriber;

use App\Application\Service\EntityManagerService;
use App\Domain\SystemEvent\EventSubscriber\VersionSystemEventSubscriber;
use App\Domain\Work\Service\WorkService;
use Symfony\Component\EventDispatcher\EventDispatcher;

class VersionSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected static string $classSubscriber = VersionSystemEventSubscriber::class;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new static::$classSubscriber(
            $kernel->getContainer()->get(EntityManagerService::class),
            $kernel->getContainer()->get(WorkService::class)
        );
    }
}

