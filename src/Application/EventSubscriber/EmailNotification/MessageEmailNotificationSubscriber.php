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

namespace App\Application\EventSubscriber\EmailNotification;

use App\Application\EventSubscriber\Events;
use App\Application\Messenger\EmailNotification\EmailNotificationMessage;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::MESSAGE_CREATE => 'onMessageCreate'
        ];
    }

    public function onMessageCreate(ConversationMessageGenericEvent $event): void
    {
        $conversationMessage = $event->conversationMessage;
        $conversation = $conversationMessage->getConversation();

        foreach ($conversation->getParticipants() as $participant) {
            if ($conversationMessage->getOwner()->getId() === $participant->getUser()->getId()) {
                continue;
            }

            $toUser = $participant->getUser();

            $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
                'locale' => $toUser->getLocale() ?? $this->locale,
                'subject' => 'subject.message_create',
                'to' => $toUser->getEmail(),
                'from' => $this->sender,
                'template' => 'message_create',
                'templateParameters' => [
                    'messageOwner' => $conversationMessage->getOwner()->getFullNameDegree(),
                    'conversationId' => $conversation->getId()
                ]
            ]);

            $this->addEmailNotificationToQueue($emailNotificationToQueueData);
        }
    }
}
