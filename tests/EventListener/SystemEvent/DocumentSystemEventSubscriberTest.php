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

namespace App\Tests\EventListener\SystemEvent;

use App\EventSubscriber\SystemEvent\DocumentSystemEventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DocumentSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected string $classSubscriber = DocumentSystemEventSubscriber::class;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new $this->classSubscriber(
            $kernel->getContainer()->get('app.entity_manager'),
            $kernel->getContainer()->get('app.user_work')
        );
    }
}

