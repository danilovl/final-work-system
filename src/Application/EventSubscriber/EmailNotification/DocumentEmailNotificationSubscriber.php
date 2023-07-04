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

namespace App\Application\EventSubscriber\EmailNotification;

use App\Application\Constant\WorkUserTypeConstant;
use App\Application\EventSubscriber\Events;
use App\Application\Messenger\EmailNotification\EmailNotificationMessage;
use App\Application\Service\TranslatorService;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserWorkService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

class DocumentEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserFacade $userFacade,
        protected Environment $twig,
        protected TranslatorService $translator,
        protected EmailNotificationQueueFactory $emailNotificationQueueFactory,
        protected ParameterServiceInterface $parameterService,
        private readonly UserWorkService $userWorkService,
        protected MessageBusInterface $bus
    ) {
        parent::__construct(
            $userFacade,
            $twig,
            $translator,
            $emailNotificationQueueFactory,
            $parameterService,
            $bus
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_DOCUMENT_CREATE => 'onDocumentCreate'
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
