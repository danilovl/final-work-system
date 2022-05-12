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

use App\Application\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\Application\EventSubscriber\Events;
use App\Application\Service\TranslatorService;
use App\Domain\EmailNotificationQueue\Factory\EmailNotificationQueueFactory;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Domain\Work\Service\WorkService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class VersionEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserFacade $userFacade,
        protected Environment $twig,
        protected TranslatorService $translator,
        protected EmailNotificationQueueFactory $emailNotificationQueueFactory,
        protected ParameterServiceInterface $parameterService,
        private readonly WorkService $workService,
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
            Events::NOTIFICATION_VERSION_CREATE => 'onVersionCreate',
            Events::NOTIFICATION_VERSION_EDIT => 'onVersionEdit'
        ];
    }

    private function onBaseEvent(
        VersionGenericEvent $event,
        string $subject,
        string $template
    ): void {
        $media = $event->media;
        $work = $media->getWork();
        $workUsers = $this->workService->getAllUsers($work);

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() === $media->getOwner()->getId()) {
                continue;
            }

            $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
                'locale' => $user->getLocale() ?? $this->locale,
                'subject' => $subject,
                'to' => $user->getEmail(),
                'from' => $this->sender,
                'template' => $template,
                'templateParameters' => [
                    'mediaOwner' => $media->getOwner()->getFullNameDegree(),
                    'mediaName' => $media->getName(),
                    'workTitle' => $work->getTitle(),
                    'workId' => $work->getId()
                ]
            ]);

            $this->addEmailNotificationToQueue($emailNotificationToQueueData);
        }
    }

    public function onVersionCreate(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, 'subject.version_create', 'work_version_create');
    }

    public function onVersionEdit(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, 'subject.version_edit', 'work_version_edit');
    }
}
