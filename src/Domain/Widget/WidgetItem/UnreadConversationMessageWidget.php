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

namespace App\Domain\Widget\WidgetItem;

use App\Application\Service\TwigRenderService;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;

class UnreadConversationMessageWidget extends BaseWidget
{
    private const int COUNT_VIEW = 6;

    private ?User $user = null;

    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        private readonly UserService $userService,
        private readonly ConversationMessageFacade $conversationMessageFacade
    ) {}

    public function getRenderParameters(): array
    {
        $user = $this->user ?? $this->userService->getUser();

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
        return $this->twigRenderService->render('application/widget/conversation_message.html.twig', $this->getRenderParameters());
    }

    public function renderForUser(User $user): string
    {
        $this->user = $user;
        $parameters = $this->getRenderParameters();
        $this->user = null;

        return $this->twigRenderService->render('application/widget/conversation_message.html.twig', $parameters);
    }
}
