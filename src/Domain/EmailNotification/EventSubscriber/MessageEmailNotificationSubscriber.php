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

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
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
        $conversation = $conversationMessage->getConversation();
        $owner = $conversation->getOwner();

        $participants = $conversation->getParticipantsExceptUsers([$owner]);

        foreach ($participants as $participant) {
            $toUser = $participant->getUser();

            $emailNotificationToQueueData = new EmailNotificationMessage(
                locale: $toUser->getLocale() ?? $this->locale,
                subject: 'subject.message_create',
                to: $toUser->getEmail(),
                from: $this->sender,
                template: 'message_create',
                templateParameters: [
                    'messageOwner' => $conversationMessage->getOwner()->getFullNameDegree(),
                    'conversationId' => $conversation->getId()
                ]
            );

            $this->addEmailNotificationToQueue($emailNotificationToQueueData);
        }
    }
}
