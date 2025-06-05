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

use App\Domain\EmailNotification\EventSubscriber\TaskEmailNotificationSubscriber;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;

class TaskEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = TaskEmailNotificationSubscriber::class;

    protected TaskEmailNotificationSubscriber $taskEmailNotificationSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new TaskEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        );

        $this->taskEmailNotificationSubscriber = $this->subscriber;
    }

    public function testOnResetPasswordToken(): void
    {
        $this->expectNotToPerformAssertions();

        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setFirstname('test');
        $user->setLastname('test');

        $work = new Work;
        $work->setId(1);
        $work->setTitle('test');
        $work->setAuthor($user);
        $work->setSupervisor($user);

        $task = new Task;
        $task->setOwner($user);
        $task->setName('name');
        $task->setWork($work);

        $event = new TaskGenericEvent($task);

        $this->taskEmailNotificationSubscriber->onTaskCreate($event);
        $this->taskEmailNotificationSubscriber->onTaskEdit($event);
        $this->taskEmailNotificationSubscriber->onTaskComplete($event);
        $this->taskEmailNotificationSubscriber->onTaskInComplete($event);
        $this->taskEmailNotificationSubscriber->onTaskNotifyComplete($event);
        $this->taskEmailNotificationSubscriber->onTaskNotifyInComplete($event);
        $this->taskEmailNotificationSubscriber->onTaskReminderDeadlineCreate($event);
    }
}
