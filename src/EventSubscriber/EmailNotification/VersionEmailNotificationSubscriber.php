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

use App\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Entity\User;
use App\EventSubscriber\Events;
use App\Model\EmailNotificationQueue\EmailNotificationQueueFactory;
use App\Model\User\UserFacade;
use App\Service\WorkService;
use Danilovl\ParameterBundle\Services\ParameterService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class VersionEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserFacade $userFacade,
        protected Environment $twig,
        protected TranslatorInterface $translator,
        protected EmailNotificationQueueFactory $emailNotificationQueueFactory,
        protected ParameterService $parameterService,
        private WorkService $workService
    ) {
        parent::__construct(
            $userFacade,
            $twig,
            $translator,
            $emailNotificationQueueFactory,
            $parameterService
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

        $subject = $this->trans($subject);
        $body = $this->twig->render($this->getTemplate($template), [
            'media' => $media,
            'work' => $work
        ]);

        $workUsers = $this->workService->getAllUsers($work);

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $to = $user->getEmail();
                $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
            }
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