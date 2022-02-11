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

namespace App\Tests\Application\EventListener\SystemEvent;

use App\Application\Service\EntityManagerService;
use App\Tests\Application\EventListener\BaseEventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BaseSystemEventSubscriber extends BaseEventSubscriber
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new $this->classSubscriber(
            $kernel->getContainer()->get(EntityManagerService::class)
        );
    }
}
