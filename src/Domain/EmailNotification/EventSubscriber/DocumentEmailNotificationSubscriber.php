<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\EmailNotification\Provider\{
    EmailNotificationAddToQueueProvider,
    EmailNotificationEnableMessengerProvider
};
use Override;
use App\Application\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\EmailNotification\Factory\EmailNotificationFactory;
use App\Domain\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DocumentEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserFacade $userFacade,
        protected TwigRenderService $twigRenderService,
        protected TranslatorService $translator,
        protected EmailNotificationFactory $emailNotificationFactory,
        protected ParameterServiceInterface $parameterService,
        private readonly UserWorkService $userWorkService,
        protected MessageBusInterface $bus,
        protected EmailNotificationAddToQueueProvider $emailNotificationAddToQueueProvider,
        protected EmailNotificationEnableMessengerProvider $emailNotificationEnableMessengerProvider
    ) {
        parent::__construct(
            $userFacade,
            $twigRenderService,
            $translator,
            $emailNotificationFactory,
            $parameterService,
            $bus,
            $emailNotificationAddToQueueProvider,
            $emailNotificationEnableMessengerProvider
        );
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::DOCUMENT_CREATE => 'onDocumentCreate'
        ];
    }

    public function onDocumentCreate(MediaGenericEvent $event): void
    {
        $media = $event->media;
        $owner = $media->getOwner();

        $recipientArray = $this->userWorkService->getActiveAuthor($owner, WorkUserTypeConstant::SUPERVISOR->value);

        $templateParameters = [
            'mediaOwner' => $media->getOwner()->getFullNameDegree(),
            'mediaName' => $media->getName()
        ];

        foreach ($recipientArray as $user) {
            $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
                'locale' => $user->getLocale() ?? $this->locale,
                'subject' => 'subject.document_create',
                'to' => $user->getEmail(),
                'from' => $this->sender,
                'template' => 'document_create',
                'templateParameters' => $templateParameters
            ]);

            $this->addEmailNotificationToQueue($emailNotificationToQueueData);
        }
    }
}
