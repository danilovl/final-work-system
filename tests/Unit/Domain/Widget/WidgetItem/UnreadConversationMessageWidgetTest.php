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

use App\Domain\Conversation\Facade\ConversationMessageFacade;
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
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->any())
            ->method('getUser')
            ->willReturn(new User);

        $twigRenderService = $this->createMock(TwigRenderService::class);
        $twigRenderService->expects($this->any())
            ->method('render')
            ->willReturn('content');

        $conversationMessageFacade = $this->createMock(ConversationMessageFacade::class);
        $conversationMessageFacade->expects($this->any())
            ->method('getTotalUnreadMessagesByUser')
            ->willReturn(1);

        $conversationMessageFacade->expects($this->any())
            ->method('getUnreadMessagesByUser')
            ->willReturn([]);

        $this->widget = new UnreadConversationMessageWidget(
            $twigRenderService,
            $userService,
            $conversationMessageFacade
        );
    }

    public function testRender(): void
    {
        $this->assertEquals('content', $this->widget->render());
    }
}
