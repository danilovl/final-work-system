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

use App\Application\EventSubscriber\EmailNotification\VersionEmailNotificationSubscriber;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Entity\User;
use App\Domain\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkService;

class VersionEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = VersionEmailNotificationSubscriber::class;
    protected readonly VersionEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new VersionEmailNotificationSubscriber(
            $this->userFacade,
            $this->twig,
            $this->translator,
            $this->emailNotificationQueueFactory,
            $this->parameterService,
            new WorkService,
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

        $media = new Media;
        $media->setName('test');
        $media->setOwner($user);
        $media->setWork($work);

        $event = new VersionGenericEvent($media);

        $this->subscriber->onVersionCreate($event);
        $this->subscriber->onVersionEdit($event);

        $this->assertTrue(true);
    }
}
