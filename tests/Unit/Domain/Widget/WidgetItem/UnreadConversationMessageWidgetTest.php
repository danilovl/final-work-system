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

namespace App\Tests\Unit\Domain\Widget\WidgetItem;

use App\Infrastructure\Interfaces\Bus\{QueryBusInterface, QueryInterface};
use App\Domain\ConversationMessage\Bus\Query\TotalUnreadMessagesByUser\{
    GetTotalUnreadMessagesByUserQuery,
    GetTotalUnreadMessagesByUserQueryResult
};
use App\Domain\ConversationMessage\Bus\Query\UnreadMessagesByUser\GetUnreadMessagesByUserQueryResult;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\UnreadConversationMessageWidget;
use App\Infrastructure\Service\TwigRenderService;
use PHPUnit\Framework\TestCase;

class UnreadConversationMessageWidgetTest extends TestCase
{
    private UnreadConversationMessageWidget $widget;

    protected function setUp(): void
    {
        $userService = $this->createStub(UserService::class);
        $userService
            ->method('getUser')
            ->willReturn(new User);

        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $queryBus = $this->createStub(QueryBusInterface::class);
        $queryBus
            ->method('handle')
            ->willReturnCallback(static fn (QueryInterface $query): object => match (true) {
                $query instanceof GetTotalUnreadMessagesByUserQuery => new GetTotalUnreadMessagesByUserQueryResult(1),
                default => new GetUnreadMessagesByUserQueryResult([])
            });

        $this->widget = new UnreadConversationMessageWidget(
            $twigRenderService,
            $userService,
            $queryBus
        );
    }

    public function testRender(): void
    {
        $this->assertEquals('content', $this->widget->render());
    }
}
