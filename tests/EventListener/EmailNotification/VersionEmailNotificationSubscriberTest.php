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

namespace App\Tests\EventListener\EmailNotification;

use App\EventSubscriber\EmailNotification\VersionEmailNotificationSubscriber;
use App\Model\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Model\User\Facade\UserFacade;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
            $kernel->getContainer()->get('app.translator'),
            $kernel->getContainer()->get(EmailNotificationQueueFactory::class),
            $kernel->getContainer()->get('danilovl.parameter'),
            $kernel->getContainer()->get('app.work'),
            $kernel->getContainer()->get('old_sound_rabbit_mq.email_notification_producer')
        );
    }
}
