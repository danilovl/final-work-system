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

use App\Domain\SystemEvent\EventSubscriber\MessageSystemEventSubscriber;

class MessageSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected static string $classSubscriber = MessageSystemEventSubscriber::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new MessageSystemEventSubscriber($this->entityManagerService);
    }
}
