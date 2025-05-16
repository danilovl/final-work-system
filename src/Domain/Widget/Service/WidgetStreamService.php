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

namespace App\Domain\Widget\Service;

use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\SystemEvent\Facade\SystemEventFacade;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\{
    UnreadSystemEventWidget,
    UnreadConversationMessageWidget
};
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;

class WidgetStreamService
{
    private array $lastCountUnread = [
        'conversation' => 0,
        'systemEvent' => 0
    ];

    public function __construct(
        private readonly ParameterServiceInterface $parameterService,
        private readonly UserService $userService,
        private readonly ConversationMessageFacade $conversationMessageFacade,
        private readonly SystemEventFacade $systemEventFacade,
        private readonly UnreadConversationMessageWidget $conversationMessageWidget,
        private readonly UnreadSystemEventWidget $unreadSystemEventWidget,
    ) {}

    private function getData(): ?array
    {
        $conversation = null;
        $systemEvent = null;
        $user = $this->userService->getUser();

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
        $sleepSecond = $this->parameterService->getInt('event_source.widget.top_nav.sleep');

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
