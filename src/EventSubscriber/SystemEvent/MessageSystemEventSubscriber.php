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
use App\Model\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    SystemEvent,
    SystemEventType,
    SystemEventRecipient,
    ConversationParticipant
};

class MessageSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_MESSAGE_CREATE => 'onMessageCreate'
        ];
    }

    public function onMessageCreate(ConversationMessageGenericEvent $event): void
    {
        $conversationMessage = $event->conversationMessage;
        $massageOwner = $conversationMessage->getOwner();
        $conversation = $conversationMessage->getConversation();

        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::MESSAGE_CREATE);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($massageOwner);

        if ($conversation->getWork()) {
            $systemEvent->setWork($conversation->getWork());
        }

        $systemEvent->setConversation($conversation);
        $systemEvent->setType($systemEventType);

        $participantArray = $conversation->getParticipants();

        /** @var ConversationParticipant $participant */
        foreach ($participantArray as $participant) {
            if ($massageOwner->getId() !== $participant->getUser()->getId()) {
                $recipientAuthor = new SystemEventRecipient;
                $recipientAuthor->setRecipient($participant->getUser());
                $systemEvent->addRecipient($recipientAuthor);
            }
        }

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
