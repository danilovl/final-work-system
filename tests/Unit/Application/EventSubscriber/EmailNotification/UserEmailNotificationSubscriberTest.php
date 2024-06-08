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

use App\Application\EventSubscriber\EmailNotification\UserEmailNotificationSubscriber;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;

class UserEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = UserEmailNotificationSubscriber::class;
    protected readonly UserEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new UserEmailNotificationSubscriber(
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
        $user->setUsername('username');
        $user->setPassword('password');

        $event = new UserGenericEvent($user);

        $this->subscriber->onUserCreate($event);
        $this->subscriber->onUserEdit($event);

        $this->assertTrue(true);
    }
}
