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

use App\Application\Interfaces\Bus\{
    QueryBusInterface,
    QueryInterface
};
use App\Domain\SystemEvent\Bus\Query\TotalUnreadSystemEventsByRecipient\{
    GetTotalUnreadSystemEventsByRecipientQuery,
    GetTotalUnreadSystemEventsByRecipientQueryResult
};
use App\Domain\SystemEventRecipient\Bus\Query\UnreadSystemEventsByRecipient\GetUnreadSystemEventsByRecipientQueryResult;
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

        $queryBus = $this->createStub(QueryBusInterface::class);
        $queryBus
            ->method('handle')
            ->willReturnCallback(static fn (QueryInterface $query): object => match (true) {
                $query instanceof GetTotalUnreadSystemEventsByRecipientQuery => new GetTotalUnreadSystemEventsByRecipientQueryResult(1),
                default => new GetUnreadSystemEventsByRecipientQueryResult([])
            });

        $this->widget = new UnreadSystemEventWidget(
            $twigRenderService,
            $userService,
            $queryBus
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
