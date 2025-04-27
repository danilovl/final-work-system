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

use App\Domain\EmailNotification\EventSubscriber\SecurityEmailNotificationSubscriber;
use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\ResetPassword\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\Domain\User\Entity\User;

class SecurityEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = SecurityEmailNotificationSubscriber::class;

    protected SecurityEmailNotificationSubscriber $securityEmailNotificationSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new SecurityEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        );

        $this->securityEmailNotificationSubscriber = $this->subscriber;
    }

    public function testOnResetPasswordToken(): void
    {
        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setFirstname('test');
        $user->setLastname('test');

        $resetPassword = new ResetPassword;
        $resetPassword->setUser($user);
        $resetPassword->setHashedToken('hashed');

        $event = new ResetPasswordGenericEvent($resetPassword, 1_000);

        $this->securityEmailNotificationSubscriber->onResetPasswordToken($event);

        $this->assertTrue(true);
    }
}
