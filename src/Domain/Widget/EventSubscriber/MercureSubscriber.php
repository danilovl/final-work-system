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

namespace App\Domain\Widget\EventSubscriber;

use App\Infrastructure\Event\EventDispatcher\GenericEvent\EntityCreateEvent;
use App\Application\EventSubscriber\Events;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\{
    UnreadConversationMessageWidget,
    UnreadSystemEventWidget
};
use Danilovl\HashidsBundle\Service\HashidsService;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\{
    Update,
    HubInterface
};
use App\Infrastructure\Event\InfrastructureEvents;

readonly class MercureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private HashidsService $hashidsService,
        private UnreadConversationMessageWidget $unreadConversationMessageWidget,
        private UnreadSystemEventWidget $unreadSystemEventWidget,
        private HubInterface $hub
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::MESSAGE_CREATE => ['onMessageCreateUnreadConversationMessageWidget', -10],
            InfrastructureEvents::ENTITY_CREATE_ASYNC => ['onSystemEventCreate', -10]
        ];
    }

    public function onMessageCreateUnreadConversationMessageWidget(ConversationMessageGenericEvent $event): void
    {
        $user = $this->userService->getUser();

        $conversationMessage = $event->conversationMessage;
        $participants = $conversationMessage->getConversation()->getParticipantsExceptUsers([$user]);

        foreach ($participants as $participant) {
            $participantUser = $participant->getUser();

            $update = new Update(
                'unread-conversation-message-widget/' . $this->hashidsService->encode($participantUser->getId()),
                (string) json_encode([
                    'content' => $this->unreadConversationMessageWidget->renderForUser($participantUser)
                ]),
            );

            $this->hub->publish($update);
        }
    }

    public function onSystemEventCreate(EntityCreateEvent $genericEvent): void
    {
        $object = $genericEvent->object;
        if (!$object instanceof SystemEvent) {
            return;
        }

        $systemEventRecipients = $object->getRecipient();

        foreach ($systemEventRecipients as $systemEventRecipient) {
            $user = $systemEventRecipient->getRecipient();

            $update = new Update(
                'unread-system-event-widget/' . $this->hashidsService->encode($user->getId()),
                (string) json_encode([
                    'content' => $this->unreadSystemEventWidget->renderForUser($user)
                ]),
            );

            $this->hub->publish($update);
        }
    }
}
