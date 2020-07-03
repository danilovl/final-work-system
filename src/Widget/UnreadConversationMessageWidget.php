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

namespace App\Widget;

use App\Model\Conversation\ConversationMessageFacade;
use App\Services\UserService;
use Twig\Environment;

class UnreadConversationMessageWidget extends BaseWidget
{
    private const COUNT_VIEW = 6;

    private Environment $environment;
    private UserService $userService;
    private ConversationMessageFacade $conversationMessageFacade;

    public function __construct(
        Environment $environment,
        UserService $userService,
        ConversationMessageFacade $conversationMessageFacade
    ) {
        $this->environment = $environment;
        $this->conversationMessageFacade = $conversationMessageFacade;
        $this->userService = $userService;
    }

    public function getRenderParameters(): array
    {
        $user = $this->userService->getUser();
        $countUnreadConversationMessage = $this->conversationMessageFacade
            ->getTotalUnreadMessagesByUser($user);

        $conversationMessages = $this->conversationMessageFacade
            ->getUnreadMessagesByUser($user, self::COUNT_VIEW);

        return [
            'countUnreadConversationMessage' => $countUnreadConversationMessage,
            'unreadConversationMessages' => $conversationMessages,
        ];
    }

    public function render(): string
    {
        return $this->environment
            ->render('widget/conversation_message.html.twig', $this->getRenderParameters());
    }
}