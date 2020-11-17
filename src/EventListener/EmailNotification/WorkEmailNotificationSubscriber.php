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

namespace App\EventListener\EmailNotification;

use App\EventDispatcher\GenericEvent\WorkGenericEvent;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_WORK_CREATE => 'onWorkCreate',
            Events::NOTIFICATION_WORK_EDIT => 'onWorkEdit',
        ];
    }

    public function onWorkCreate(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $subject = $this->trans('subject.work_create');
        $author = $work->getAuthor();
        $opponent = $work->getOpponent();
        $consultant = $work->getConsultant();

        $to = null;
        $bodyParams = [];
        if ($author !== null) {
            $to = $author->getEmail();
            $bodyParams = [
                'work' => $work,
                'role' => 'autora'
            ];
        }

        if ($opponent !== null) {
            $to = $opponent->getEmail();
            $bodyParams = [
                'work' => $work,
                'role' => 'opponenta'
            ];
        }

        if ($consultant !== null) {
            $to = $consultant->getEmail();
            $bodyParams = [
                'work' => $work,
                'role' => 'konzultanta'
            ];
        }

        if ($to !== null) {
            $body = $this->twig->render($this->getTemplate('work_create'), $bodyParams);
            $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
        }
    }

    public function onWorkEdit(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $subject = $this->trans('subject.work_edit');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('work_edit'), [
            'work' => $work
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }
}