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

use App\Constant\WorkUserTypeConstant;
use App\EventSubscriber\Events;
use App\Constant\SystemEventTypeConstant;
use App\Model\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\Model\SystemEvent\Entity\SystemEvent;
use App\Model\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Model\SystemEventType\Entity\SystemEventType;
use App\Model\User\Entity\User;
use App\Service\EntityManagerService;
use App\Model\User\Service\UserWorkService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        EntityManagerService $entityManagerService,
        private UserWorkService $userWorkService
    ) {
        parent::__construct($entityManagerService);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_DOCUMENT_CREATE => 'onDocumentCreate'
        ];
    }

    public function onDocumentCreate(MediaGenericEvent $event): void
    {
        $media = $event->media;
        $owner = $media->getOwner();

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::DOCUMENT_CREATE);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($owner);
        $systemEvent->setMedia($media);
        $systemEvent->setType($systemEventType);

        $recipientArray = $this->userWorkService->getActiveAuthor($owner, WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $recipientAuthor = new SystemEventRecipient;
            $recipientAuthor->setRecipient($user);
            $systemEvent->addRecipient($recipientAuthor);
        }

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
