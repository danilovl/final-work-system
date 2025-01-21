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

namespace App\Domain\Conversation\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Application\Service\TwigRenderService;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\UnreadConversationMessageWidget;
use Danilovl\HashidsBundle\Service\HashidsService;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\{
    HubInterface,
    Update};

readonly class MercureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private HashidsService $hashidsService,
        private UnreadConversationMessageWidget $unreadConversationMessageWidget,
        private HubInterface $hub
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::MESSAGE_CREATE => [
                ['onMessageCreateConversation', -10],
                ['onMessageCreateUnreadConversationMessageWidget', -10]
            ]
        ];
    }

    public function onMessageCreateConversation(ConversationMessageGenericEvent $event): void
    {
        $conversationMessage = $event->conversationMessage;
        $conversation = $conversationMessage->getConversation();

        $message = $this->twigRenderService->render('domain/conversation/include/chat_message.html.twig', [
            'message' => $conversationMessage
        ]);

        $update = new Update(
            'conversation/' . $this->hashidsService->encode($conversation->getId()),
            (string) json_encode(['message' => $message]),
        );

        $this->hub->publish($update);
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
}
