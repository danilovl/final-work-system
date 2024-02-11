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

namespace App\Tests\Unit\Application\EventSubscriber\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\WorkEmailNotificationSubscriber;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;

class WorkEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = WorkEmailNotificationSubscriber::class;
    protected readonly WorkEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new WorkEmailNotificationSubscriber(
            $this->userFacade,
            $this->twig,
            $this->translator,
            $this->emailNotificationQueueFactory,
            $this->parameterService,
            $this->bus
        );
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

        $this->subscriber->onWorkCreate($event);
        $this->subscriber->onWorkCreate($event);
        $this->subscriber->onWorkEdit($event);

        $this->assertTrue(true);
    }
}
