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

use App\Application\EventSubscriber\Events;
use App\Application\Messenger\EmailNotification\EmailNotificationMessage;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_WORK_CREATE => 'onWorkCreate',
            Events::NOTIFICATION_WORK_EDIT => 'onWorkEdit'
        ];
    }

    public function onWorkCreate(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $author = $work->getAuthor();
        $opponent = $work->getOpponent();
        $consultant = $work->getConsultant();

        $to = null;
        $locale = $this->locale;

        $templateParameters = [
            'workId' => $work->getId(),
            'workSupervisor' => $work->getSupervisor()->getFullNameDegree(),
        ];

        if ($author !== null) {
            $to = $author->getEmail();
            $locale = $author->getLocale() ?? $locale;

            $templateParameters['role'] = $this->translator->trans('app.text.author_like', locale: $locale);
        }

        if ($opponent !== null) {
            $to = $opponent->getEmail();
            $locale = $opponent->getLocale() ?? $locale;

            $templateParameters['role'] = $this->translator->trans('app.text.opponent_like', locale: $locale);
        }

        if ($consultant !== null) {
            $to = $consultant->getEmail();
            $locale = $consultant->getLocale() ?? $locale;

            $templateParameters['role'] = $this->translator->trans('app.text.consultant_like', locale: $locale);
        }

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $locale,
            'subject' => 'subject.work_create',
            'to' => $to,
            'from' => $this->sender,
            'template' => 'work_create',
            'templateParameters' => $templateParameters
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onWorkEdit(WorkGenericEvent $event): void
    {
        $work = $event->work;
        $toUser = $work->getAuthor();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.work_edit',
            'to' => $toUser->getEmail(),
            'from' => $this->sender,
            'template' => 'work_edit',
            'templateParameters' => [
                'workId' => $work->getId(),
                'workSupervisor' => $work->getSupervisor()->getFullNameDegree()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }
}
