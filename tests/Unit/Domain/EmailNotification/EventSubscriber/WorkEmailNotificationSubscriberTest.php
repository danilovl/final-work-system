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

namespace App\Tests\Unit\Domain\EmailNotification\EventSubscriber;

use App\Domain\EmailNotification\EventSubscriber\WorkEmailNotificationSubscriber;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;

class WorkEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = WorkEmailNotificationSubscriber::class;

    protected WorkEmailNotificationSubscriber $workEmailNotificationSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new WorkEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        );

        $this->workEmailNotificationSubscriber = $this->subscriber;
    }

    public function testOnResetPasswordToken(): void
    {
        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setFirstname('test');
        $user->setLastname('test');

        $userTwo = clone $user;
        $userTwo->setId(2);

        $work = new Work;
        $work->setId(1);
        $work->setTitle('test');
        $work->setAuthor($user);
        $work->setSupervisor($userTwo);
        $work->setOpponent($userTwo);
        $work->setConsultant($userTwo);

        $event = new WorkGenericEvent($work);

        $this->workEmailNotificationSubscriber->onWorkCreate($event);
        $this->workEmailNotificationSubscriber->onWorkCreate($event);
        $this->workEmailNotificationSubscriber->onWorkEdit($event);
        $this->workEmailNotificationSubscriber->onWorkReminderDeadlineCreate($event);

        $this->assertTrue(true);
    }
}
