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

namespace App\Tests\Application\EventListener\EmailNotification;

use App\Application\Service\TranslatorService;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\User\Facade\UserFacade;
use App\Tests\Application\EventListener\BaseEventSubscriber;
use Danilovl\ParameterBundle\Service\ParameterService;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BaseEmailNotificationSubscriber extends BaseEventSubscriber
{
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
            $kernel->getContainer()->get('old_sound_rabbit_mq.email_notification_producer')
        );
    }
}