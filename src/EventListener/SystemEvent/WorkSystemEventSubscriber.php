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

namespace App\EventListener\SystemEvent;

use App\EventDispatcher\GenericEvent\WorkGenericEvent;
use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};
use App\Entity\User;

class WorkSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_WORK_CREATE => 'onWorkCreate',
            Events::SYSTEM_WORK_EDIT => 'onWorkEdit',
            Events::SYSTEM_WORK_AUTHOR_EDIT => 'onWorkAuthorEdit'
        ];
    }

    public function onWorkCreate(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $systemEvent = new SystemEvent;
        $systemEvent->setWork($work);
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::WORK_CREATE)
        );

        $workUsers = $work->getAllUsers();

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() === $work->getSupervisor()->getId()) {
                continue;
            }

            $recipient = new SystemEventRecipient;
            $recipient->setRecipient($user);
            $systemEvent->addRecipient($recipient);
        }

        $this->em->persistAndFlush($systemEvent);
    }

    public function onWorkEdit(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $systemEvent = new SystemEvent;
        $systemEvent->setWork($work);
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::WORK_EDIT)
        );

        $recipientAuthor = new SystemEventRecipient;
        $recipientAuthor->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipientAuthor);

        if ($work->getOpponent()) {
            $recipientOpponent = new SystemEventRecipient;
            $recipientOpponent->setRecipient($work->getOpponent());
            $systemEvent->addRecipient($recipientOpponent);
        }

        if ($work->getConsultant()) {
            $recipientConsultant = new SystemEventRecipient;
            $recipientConsultant->setRecipient($work->getConsultant());
            $systemEvent->addRecipient($recipientConsultant);
        }

        $this->em->persistAndFlush($systemEvent);
    }

    public function onWorkAuthorEdit(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::USER_EDIT)
        );
        $systemEvent->setWork($work);

        $recipientAuthor = new SystemEventRecipient;
        $recipientAuthor->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipientAuthor);

        $this->em->persistAndFlush($systemEvent);
    }
}
