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

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::WORK_CREATE => 'onWorkCreate',
            Events::WORK_EDIT => 'onWorkEdit',
            Events::WORK_REMIND_DEADLINE_CREATE => 'onWorkReminderDeadlineCreate'
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

    public function onWorkReminderDeadlineCreate(WorkGenericEvent $event): void
    {
        $work = $event->work;
        $toUser = $work->getAuthor();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.work_reminder_deadline',
            'to' => $toUser->getEmail(),
            'from' => $this->sender,
            'template' => 'work_reminder_deadline',
            'templateParameters' => [
                'workId' => $work->getId(),
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }
}
