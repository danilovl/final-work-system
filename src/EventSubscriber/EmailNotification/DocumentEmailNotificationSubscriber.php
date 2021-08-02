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

namespace App\EventSubscriber\EmailNotification;

use App\Constant\WorkUserTypeConstant;
use App\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Entity\User;
use App\EventSubscriber\Events;
use App\Model\EmailNotificationQueue\EmailNotificationQueueFactory;
use App\Model\User\UserFacade;
use App\Service\User\UserWorkService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DocumentEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserFacade $userFacade,
        protected Environment $twig,
        protected TranslatorInterface $translator,
        protected EmailNotificationQueueFactory $emailNotificationQueueFactory,
        protected ParameterServiceInterface $parameterService,
        private UserWorkService $userWorkService,
        protected ProducerInterface $emailNotificationProducer
    ) {
        parent::__construct(
            $userFacade,
            $twig,
            $translator,
            $emailNotificationQueueFactory,
            $parameterService,
            $emailNotificationProducer
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

        $recipientArray = $this->userWorkService->getActiveAuthor($owner, WorkUserTypeConstant::SUPERVISOR);

        $templateParameters = [
            'mediaOwner' => $media->getOwner()->getFullNameDegree(),
            'mediaName' => $media->getName()
        ];

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
                'locale' => $this->locale,
                'subject' => $this->trans('subject.document_create'),
                'to' => $user->getEmail(),
                'from' => $this->sender,
                'template' => 'document_create',
                'templateParameters' => $templateParameters
            ]);

            $this->addEmailNotificationToQueue($emailNotificationToQueueData);
        }
    }
}
