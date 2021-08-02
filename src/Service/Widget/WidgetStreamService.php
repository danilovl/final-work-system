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

namespace App\Service\Widget;

use App\Model\Conversation\ConversationMessageFacade;
use App\Model\SystemEvent\SystemEventFacade;
use App\Service\User\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTime;
use App\Widget\{
    UnreadSystemEventWidget,
    UnreadConversationMessageWidget
};

class WidgetStreamService
{
    private ?DateTime $date;
    private array $lastCountUnread = [
        'conversation' => 0,
        'systemEvent' => 0
    ];

    public function __construct(
        private ParameterServiceInterface $parameterService,
        private UserService $userService,
        private ConversationMessageFacade $conversationMessageFacade,
        private SystemEventFacade $systemEventFacade,
        private UnreadConversationMessageWidget $conversationMessageWidget,
        private UnreadSystemEventWidget $unreadSystemEventWidget,
    ) {
    }

    private function getData(): ?array
    {
        $conversation = null;
        $systemEvent = null;
        $user = $this->userService->getUser();
        $this->date = $this->date ?? new DateTime;

        $countUnreadConversationMessage = $this->conversationMessageFacade->getTotalUnreadMessagesByUser($user);
        if ($this->lastCountUnread['conversation'] !== $countUnreadConversationMessage) {
            $conversation = $this->conversationMessageWidget->render();

            $this->lastCountUnread['conversation'] = $countUnreadConversationMessage;
        }

        $countUnreadSystemEvents = $this->systemEventFacade->getTotalUnreadSystemEventsByRecipient($user);
        if ($this->lastCountUnread['systemEvent'] !== $countUnreadSystemEvents) {
            $systemEvent = $this->unreadSystemEventWidget->render();

            $this->lastCountUnread['systemEvent'] = $countUnreadSystemEvents;
        }

        if ($conversation === null && $systemEvent === null) {
            return null;
        }

        return [
            'conversation' => $conversation,
            'systemEvent' => $systemEvent
        ];
    }

    public function handle(): callable
    {
        $sleepSecond = $this->parameterService->get('event_source.widget.top_nav.sleep');

        return function () use ($sleepSecond): void {
            while (true) {
                echo 'data: ' . json_encode($this->getData()) . "\n\n";
                ob_flush();
                flush();
                sleep($sleepSecond);
            }
        };
    }
}
