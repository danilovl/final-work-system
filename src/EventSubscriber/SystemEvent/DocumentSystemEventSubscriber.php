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
use App\EventDispatcher\GenericEvent\MediaGenericEvent;
use App\EventSubscriber\Events;
use App\Constant\SystemEventTypeConstant;
use App\Service\{
    UserWorkService,
    EntityManagerService
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    User,
    SystemEvent,
    SystemEventRecipient,
    SystemEventType
};

class DocumentSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected EntityManagerService $em,
        private UserWorkService $userWorkService
    ) {
        parent::__construct($em);
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

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($owner);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::DOCUMENT_CREATE)
        );

        $recipientArray = $this->userWorkService->getActiveAuthor($owner, WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $recipientAuthor = new SystemEventRecipient;
            $recipientAuthor->setRecipient($user);
            $systemEvent->addRecipient($recipientAuthor);
        }

        $this->em->persistAndFlush($systemEvent);
    }
}
