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

use App\Domain\SystemEvent\Facade\{
    SystemEventFacade,
    SystemEventRecipientFacade
};
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\UnreadSystemEventWidget;
use App\Infrastructure\Service\TwigRenderService;
use PHPUnit\Framework\TestCase;

class UnreadSystemEventWidgetTest extends TestCase
{
    private UnreadSystemEventWidget $widget;

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

        $systemEventFacade = $this->createStub(SystemEventFacade::class);
        $systemEventFacade
            ->method('getTotalUnreadSystemEventsByRecipient')
            ->willReturn(1);

        $systemEventRecipientFacade = $this->createStub(SystemEventRecipientFacade::class);
        $systemEventRecipientFacade
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
