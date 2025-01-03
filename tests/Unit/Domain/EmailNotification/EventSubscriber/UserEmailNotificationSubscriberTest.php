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

use App\Domain\EmailNotification\EventSubscriber\UserEmailNotificationSubscriber;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;

class UserEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = UserEmailNotificationSubscriber::class;

    protected UserEmailNotificationSubscriber $userEmailNotificationSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new UserEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        );

        $this->userEmailNotificationSubscriber = $this->subscriber;
    }

    public function testOnResetPasswordToken(): void
    {
        $this->expectNotToPerformAssertions();

        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setUsername('username');
        $user->setPassword('password');

        $event = new UserGenericEvent($user);

        $this->userEmailNotificationSubscriber->onUserCreate($event);
        $this->userEmailNotificationSubscriber->onUserEdit($event);
    }
}
