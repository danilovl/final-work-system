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

use App\Infrastructure\Service\TwigRenderService;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\ConversationMessage\Bus\Query\UnreadMessagesByUser\{GetUnreadMessagesByUserQuery, GetUnreadMessagesByUserQueryResult};
use App\Domain\ConversationMessage\Bus\Query\TotalUnreadMessagesByUser\{GetTotalUnreadMessagesByUserQuery, GetTotalUnreadMessagesByUserQueryResult};

class UnreadConversationMessageWidget extends BaseWidget
{
    private const int COUNT_VIEW = 6;

    private ?User $user = null;

    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        private readonly UserService $userService,
        private readonly QueryBusInterface $queryBus
    ) {}

    public function getRenderParameters(): array
    {
        $user = $this->user ?? $this->userService->getUser();

        $getTotalUnreadMessagesByUserQuery = GetTotalUnreadMessagesByUserQuery::create($user);
        /** @var GetTotalUnreadMessagesByUserQueryResult $getTotalUnreadMessagesByUserQueryResult */
        $getTotalUnreadMessagesByUserQueryResult = $this->queryBus->handle($getTotalUnreadMessagesByUserQuery);
        $countUnreadConversationMessage = $getTotalUnreadMessagesByUserQueryResult->count;

        $getUnreadMessagesByUserQuery = GetUnreadMessagesByUserQuery::create(
            user: $user,
            limit: self::COUNT_VIEW
        );
        /** @var GetUnreadMessagesByUserQueryResult $getUnreadMessagesByUserQueryResult */
        $getUnreadMessagesByUserQueryResult = $this->queryBus->handle($getUnreadMessagesByUserQuery);
        $unreadConversationMessages = $getUnreadMessagesByUserQueryResult->messages;

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
