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

use App\Application\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\Application\EventSubscriber\Events;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_MESSAGE_CREATE => 'onMessageCreate'
        ];
    }

    public function onMessageCreate(ConversationMessageGenericEvent $event): void
    {
        $conversationMessage = $event->conversationMessage;
        $conversation = $conversationMessage->getConversation();

        /** @var ConversationParticipant $participant */
        foreach ($conversation->getParticipants() as $participant) {
            if ($conversationMessage->getOwner()->getId() === $participant->getUser()->getId()) {
                continue;
            }

            $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
                'locale' => $this->locale,
                'subject' => $this->trans('subject.message_create'),
                'to' => $participant->getUser()->getEmail(),
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
