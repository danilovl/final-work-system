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

namespace App\Tests\Kernel\Domain\SystemEvent\EventSubscriber;

use App\Application\Service\EntityManagerService;
use App\Domain\SystemEvent\EventSubscriber\DocumentSystemEventSubscriber;
use App\Domain\User\Service\UserWorkService;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DocumentSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected static string $classSubscriber = DocumentSystemEventSubscriber::class;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new static::$classSubscriber(
            $kernel->getContainer()->get(EntityManagerService::class),
            $kernel->getContainer()->get(UserWorkService::class)
        );
    }
}
