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

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\EventSubscriber\DocumentEmailNotificationSubscriber;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserWorkService;
use Doctrine\Common\Collections\ArrayCollection;

class DocumentEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = DocumentEmailNotificationSubscriber::class;

    protected DocumentEmailNotificationSubscriber $documentEmailNotificationSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $recipient = new User;
        $recipient->setEmail('test@example.com');

        $userWorkService = $this->createMock(UserWorkService::class);
        $userWorkService->expects($this->any())
            ->method('getActiveAuthor')
            ->willReturn(new ArrayCollection([$recipient]));

        $this->subscriber = new DocumentEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $userWorkService,
            $this->bus,
            $this->emailNotificationAddToQueueProvider,
            $this->emailNotificationEnableMessengerProvider
        );

        $this->documentEmailNotificationSubscriber = $this->subscriber;
    }

    public function testOnDocumentCreate(): void
    {
        $user = new User;
        $user->setFirstname('test');
        $user->setLastname('test');
        $user->setEnabledEmailNotification(true);

        $media = new Media;
        $media->setName('test');
        $media->setOwner($user);

        $event = new MediaGenericEvent($media);

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByEmail')
            ->willReturn($user);

        $this->documentEmailNotificationSubscriber->onDocumentCreate($event);

        $this->assertTrue(true);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->subscriber::getSubscribedEvents();

        $this->assertEquals('onDocumentCreate', $subscribedEvents[Events::DOCUMENT_CREATE]);
    }
}
