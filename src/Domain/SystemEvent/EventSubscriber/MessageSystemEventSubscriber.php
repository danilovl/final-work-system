<?php declare(strict_types=1);

/**
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
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use App\Domain\SystemEventType\Entity\SystemEventType;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::MESSAGE_CREATE => 'onMessageCreate'
        ];
    }

    public function onMessageCreate(ConversationMessageGenericEvent $event): void
    {
        $conversationMessage = $event->conversationMessage;
        $massageOwner = $conversationMessage->getOwner();
        $conversation = $conversationMessage->getConversation();

        /** @var SystemEventType $systemEventType */
        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::MESSAGE_CREATE->value);

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
