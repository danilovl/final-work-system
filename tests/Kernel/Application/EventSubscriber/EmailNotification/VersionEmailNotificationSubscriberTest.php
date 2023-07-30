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

use App\Application\EventSubscriber\EmailNotification\VersionEmailNotificationSubscriber;
use App\Application\Service\TranslatorService;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\User\Facade\UserFacade;
use App\Domain\Work\Service\WorkService;
use Danilovl\ParameterBundle\Service\ParameterService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;

class VersionEmailNotificationSubscriberTest extends BaseEmailNotificationSubscriber
{
    protected string $classSubscriber = VersionEmailNotificationSubscriber::class;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new $this->classSubscriber(
            $kernel->getContainer()->get(UserFacade::class),
            $kernel->getContainer()->get('twig'),
            $kernel->getContainer()->get(TranslatorService::class),
            $kernel->getContainer()->get(EmailNotificationQueueFactory::class),
            $kernel->getContainer()->get(ParameterService::class),
            $kernel->getContainer()->get(WorkService::class),
            $kernel->getContainer()->get(MessageBusInterface::class)
        );
    }
}
