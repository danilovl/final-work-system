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

use App\Application\EventSubscriber\EmailNotification\TaskEmailNotificationSubscriber;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;

class TaskEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = TaskEmailNotificationSubscriber::class;
    protected readonly TaskEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new TaskEmailNotificationSubscriber(
            $this->userFacade,
            $this->twig,
            $this->translator,
            $this->emailNotificationFactory,
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

        $this->subscriber->onTaskCreate($event);
        $this->subscriber->onTaskEdit($event);
        $this->subscriber->onTaskComplete($event);
        $this->subscriber->onTaskInComplete($event);
        $this->subscriber->onTaskNotifyComplete($event);
        $this->subscriber->onTaskNotifyInComplete($event);
        $this->subscriber->onTaskReminderDeadlineCreate($event);

        $this->assertTrue(true);
    }
}
