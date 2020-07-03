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

namespace App\EventListener\EmailNotification;

use App\Entity\{
    ConversationMessage,
    ConversationParticipant
};
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

class MessageEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_MESSAGE_CREATE => 'onMessageCreate'
        ];
    }

    public function onMessageCreate(GenericEvent $event): void
    {
        /** @var ConversationMessage $conversationMessage */
        $conversationMessage = $event->getSubject();
        $conversation = $conversationMessage->getConversation();

        $subject = $this->trans('subject.message_create');
        /** @var ConversationParticipant $parcipant */
        foreach ($conversation->getParticipants() as $parcipant) {
            $to = $parcipant->getUser()->getEmail();

            if ($conversationMessage->getOwner()->getId() !== $parcipant->getUser()->getId()) {
                $body = $this->twig->render($this->getTemplate('message_create'), [
                    'sender' => $conversationMessage->getOwner(),
                    'conversation' => $conversation
                ]);
                $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
            }
        }
    }
}