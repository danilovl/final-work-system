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

namespace App\EventSubscriber\SystemEvent;

use App\EventSubscriber\Events;
use App\Constant\SystemEventTypeConstant;
use App\Model\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Service\EntityManagerService;
use App\Model\Work\Service\WorkService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    User,
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};

class VersionSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        EntityManagerService $entityManagerService,
        private WorkService $workService
    ) {
        parent::__construct($entityManagerService);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_VERSION_CREATE => 'onVersionCreate',
            Events::SYSTEM_VERSION_EDIT => 'onVersionEdit'
        ];
    }

    private function onBaseEvent(
        VersionGenericEvent $event,
        int $systemEventTypeId,
        array $users = [true, true, true, true]
    ): void {
        $media = $event->media;
        $work = $media->getWork();

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find($systemEventTypeId);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($systemEventType);

        $workUsers = $this->workService->getUsers(...[$work, ...$users]);
        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() === $media->getOwner()->getId()) {
                continue;
            }

            $recipient = new SystemEventRecipient;
            $recipient->setRecipient($user);
            $systemEvent->addRecipient($recipient);
        }

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onVersionCreate(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, SystemEventTypeConstant::VERSION_CREATE);
    }

    public function onVersionEdit(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, SystemEventTypeConstant::VERSION_EDIT);
    }
}
