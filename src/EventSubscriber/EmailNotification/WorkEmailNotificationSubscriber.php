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

use App\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\EventSubscriber\Events;
use App\Model\Work\EventDispatcher\GenericEvent\WorkGenericEvent;
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
        $templateParameters = [
            'workId' => $work->getId(),
            'workSupervisor' => $work->getSupervisor()->getFullNameDegree(),
        ];

        if ($author !== null) {
            $to = $author->getEmail();
            $templateParameters['role'] = $this->translator->trans('app.text.author_like');
        }

        if ($opponent !== null) {
            $to = $opponent->getEmail();
            $templateParameters['role'] = $this->translator->trans('app.text.opponent_like');
        }

        if ($consultant !== null) {
            $to = $consultant->getEmail();
            $templateParameters['role'] = $this->translator->trans('app.text.consultant_like');
        }

        if ($to === null) {
            return;
        }

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.work_create'),
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

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.work_edit'),
            'to' => $work->getAuthor()->getEmail(),
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
