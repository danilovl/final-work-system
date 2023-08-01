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

namespace App\Tests\Kernel\Application\EventSubscriber\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\DocumentEmailNotificationSubscriber;
use App\Application\Service\TranslatorService;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserWorkService;
use Danilovl\ParameterBundle\Service\ParameterService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;

class DocumentEmailNotificationSubscriberTest extends BaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = DocumentEmailNotificationSubscriber::class;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new static::$classSubscriber(
            $kernel->getContainer()->get(UserFacade::class),
            $kernel->getContainer()->get('twig'),
            $kernel->getContainer()->get(TranslatorService::class),
            $kernel->getContainer()->get(EmailNotificationQueueFactory::class),
            $kernel->getContainer()->get(ParameterService::class),
            $kernel->getContainer()->get(UserWorkService::class),
            $kernel->getContainer()->get(MessageBusInterface::class)
        );
    }
}