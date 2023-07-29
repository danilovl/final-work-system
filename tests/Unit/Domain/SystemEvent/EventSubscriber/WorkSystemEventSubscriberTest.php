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
use App\Domain\Work\Service\WorkService;
use Symfony\Component\EventDispatcher\EventDispatcher;

class WorkSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected string $classSubscriber = \App\Domain\SystemEvent\EventSubscriber\WorkSystemEventSubscriber::class;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new $this->classSubscriber(
            $kernel->getContainer()->get(EntityManagerService::class),
            $kernel->getContainer()->get(WorkService::class)
        );
    }
}

