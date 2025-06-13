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

use App\Application\Service\TwigRenderService;
use App\Domain\SystemEvent\Facade\{
    SystemEventFacade,
    SystemEventRecipientFacade
};
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\UnreadSystemEventWidget;
use PHPUnit\Framework\TestCase;

class UnreadSystemEventWidgetTest extends TestCase
{
    private UnreadSystemEventWidget $widget;

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

        $systemEventFacade = $this->createMock(SystemEventFacade::class);
        $systemEventFacade->expects($this->any())
            ->method('getTotalUnreadSystemEventsByRecipient')
            ->willReturn(null);

        $systemEventRecipientFacade = $this->createMock(SystemEventRecipientFacade::class);
        $systemEventRecipientFacade->expects($this->any())
            ->method('getUnreadSystemEventsByRecipient')
            ->willReturn([]);

        $this->widget = new UnreadSystemEventWidget(
            $twigRenderService,
            $userService,
            $systemEventFacade,
            $systemEventRecipientFacade
        );
    }

    public function testRender(): void
    {
        $this->assertEquals('content', $this->widget->render());
    }

    public function testRenderForUser(): void
    {
        $this->assertEquals('content', $this->widget->renderForUser(new User));
    }
}
