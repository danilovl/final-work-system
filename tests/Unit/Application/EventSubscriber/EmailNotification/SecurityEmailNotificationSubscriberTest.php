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

use App\Application\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\Application\EventSubscriber\EmailNotification\SecurityEmailNotificationSubscriber;
use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\User\Entity\User;

class SecurityEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = SecurityEmailNotificationSubscriber::class;
    protected readonly SecurityEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new SecurityEmailNotificationSubscriber(
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

        $resetPassword = new ResetPassword;
        $resetPassword->setUser($user);

        $event = new ResetPasswordGenericEvent($resetPassword, 1000);

        $this->subscriber->onResetPasswordToken($event);

        $this->assertTrue(true);
    }
}
