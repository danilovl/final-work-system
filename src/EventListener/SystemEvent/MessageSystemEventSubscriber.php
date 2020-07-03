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

use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    SystemEvent,
    SystemEventType,
    ConversationMessage,
    ConversationParticipant,
    SystemEventRecipient
};
use Symfony\Component\EventDispatcher\GenericEvent;

class MessageSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_MESSAGE_CREATE => 'onMessageCreate'
        ];
    }

    public function onMessageCreate(GenericEvent $event): void
    {
        /** @var ConversationMessage $conversationMessage */
        $conversationMessage = $event->getSubject();
        $massageOwner = $conversationMessage->getOwner();
        $conversation = $conversationMessage->getConversation();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($massageOwner);

        if ($conversation->getWork()) {
            $systemEvent->setWork($conversation->getWork());
        }

        $systemEvent->setConversation($conversation);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::MESSAGE_CREATE)
        );

        $participantArray = $conversation->getParticipants();

        /** @var ConversationParticipant $parcipant */
        foreach ($participantArray as $parcipant) {
            if ($massageOwner->getId() !== $parcipant->getUser()->getId()) {
                $recipientAuthor = new SystemEventRecipient;
                $recipientAuthor->setRecipient($parcipant->getUser());
                $systemEvent->addRecipient($recipientAuthor);
            }
        }

        $this->em->persistAndFlush($systemEvent);
    }
}
