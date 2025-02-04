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

namespace App\Domain\SystemEvent\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Application\Service\EntityManagerService;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use App\Domain\SystemEventType\Entity\SystemEventType;
use App\Domain\User\Entity\User;
use App\Domain\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\Domain\Work\Service\WorkService;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VersionSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        EntityManagerService $entityManagerService,
        private readonly WorkService $workService
    ) {
        parent::__construct($entityManagerService);
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::WORK_VERSION_CREATE => 'onVersionCreate',
            Events::WORK_VERSION_EDIT => 'onVersionEdit'
        ];
    }

    private function onBaseEvent(
        VersionGenericEvent $event,
        int $systemEventTypeId,
        array $users = [true, true, true, true]
    ): void {
        $media = $event->media;
        $work = $media->getWorkMust();

        /** @var SystemEventType $systemEventType */
        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find($systemEventTypeId);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($systemEventType);

        $workUsers = $this->workService->getUsers($work, ...$users);
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
        $this->onBaseEvent($event, SystemEventTypeConstant::VERSION_CREATE->value);
    }

    public function onVersionEdit(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, SystemEventTypeConstant::VERSION_EDIT->value);
    }
}
