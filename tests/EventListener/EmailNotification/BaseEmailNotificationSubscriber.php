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

use App\Tests\EventListener\BaseEventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BaseEmailNotificationSubscriber extends BaseEventSubscriber
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dispatcher = new EventDispatcher;
        $this->eventSubscriber = new $this->classSubscriber(
            $kernel->getContainer()->get('app.facade.user'),
            $kernel->getContainer()->get('twig'),
            $kernel->getContainer()->get('translator'),
            $kernel->getContainer()->get('app.factory.email_notification_queue'),
            $kernel->getContainer()->get('danilovl.parameter')
        );
    }
}