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

use App\Domain\EmailNotification\EventSubscriber\VersionEmailNotificationSubscriber;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Entity\User;
use App\Domain\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class VersionEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = VersionEmailNotificationSubscriber::class;

    protected VersionEmailNotificationSubscriber $versionEmailNotificationSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new VersionEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            new WorkService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        );

        $this->versionEmailNotificationSubscriber = $this->subscriber;
    }

    public function testOnVersionEvents(): void
    {
        $this->expectNotToPerformAssertions();

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

        $this->versionEmailNotificationSubscriber->onVersionCreate($event);
        $this->versionEmailNotificationSubscriber->onVersionEdit($event);
    }
}
