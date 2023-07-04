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

namespace App\Application\EventSubscriber\SystemEvent;

use App\Application\Constant\SystemEventTypeConstant;
use App\Application\EventSubscriber\Events;
use App\Application\Service\EntityManagerService;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Entity\SystemEventType;
use App\Domain\User\Entity\User;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;
use App\Domain\Work\Service\WorkService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        EntityManagerService $entityManagerService,
        private readonly WorkService $workService
    ) {
        parent::__construct($entityManagerService);
    }

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

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::WORK_CREATE->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setWork($work);
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($systemEventType);

        $workUsers = $this->workService->getAllUsers($work);

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() === $work->getSupervisor()->getId()) {
                continue;
            }

            $recipient = new SystemEventRecipient;
            $recipient->setRecipient($user);
            $systemEvent->addRecipient($recipient);
        }

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onWorkEdit(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::WORK_EDIT->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setWork($work);
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($systemEventType);

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

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onWorkAuthorEdit(WorkGenericEvent $event): void
    {
        $work = $event->work;

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::USER_EDIT->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($systemEventType);
        $systemEvent->setWork($work);

        $recipientAuthor = new SystemEventRecipient;
        $recipientAuthor->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipientAuthor);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
