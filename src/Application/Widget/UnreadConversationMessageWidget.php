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

namespace App\Application\Widget;

use App\Application\Service\UserService;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use Twig\Environment;

class UnreadConversationMessageWidget extends BaseWidget
{
    private const COUNT_VIEW = 6;

    public function __construct(
        private readonly Environment $environment,
        private readonly UserService $userService,
        private readonly ConversationMessageFacade $conversationMessageFacade
    ) {}

    public function getRenderParameters(): array
    {
        $user = $this->userService->getUser();
        $countUnreadConversationMessage = $this->conversationMessageFacade
            ->getTotalUnreadMessagesByUser($user);

        $unreadConversationMessages = $this->conversationMessageFacade
            ->getUnreadMessagesByUser($user, self::COUNT_VIEW);

        return [
            'countUnreadConversationMessage' => $countUnreadConversationMessage,
            'unreadConversationMessages' => $unreadConversationMessages,
        ];
    }

    public function render(): string
    {
        return $this->environment->render('widget/conversation_message.html.twig', $this->getRenderParameters());
    }
}
