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

use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\WorkDeadlineNotifyWidget;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Bus\Query\WorksByAuthorStatus\GetWorksByAuthorStatusQueryResult;
use App\Infrastructure\Service\{
    TranslatorService
};
use App\Infrastructure\Service\TwigRenderService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class WorkDeadlineNotifyWidgetTest extends TestCase
{
    private UserService $userService;

    private QueryBusInterface $queryBus;

    private WorkDeadlineNotifyWidget $widget;

    protected function setUp(): void
    {
        $this->userService = $this->createStub(UserService::class);
        $this->queryBus = $this->createStub(QueryBusInterface::class);

        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $parameterService = $this->createStub(ParameterServiceInterface::class);
        $parameterService
            ->method('getString')
            ->willReturn('info');

        $translator = $this->createStub(TranslatorService::class);
        $translator
            ->method('trans')
            ->willReturn('trans');

        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $parameterService,
            $translator,
            $twigRenderService,
            $this->queryBus
        );
    }

    public function testRender(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->queryBus = $this->createMock(QueryBusInterface::class);
        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $twigRenderService,
            $this->queryBus
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $work = new Work;
        $work->setDeadline(new DateTimeImmutable('+10 days'));

        $this->queryBus
            ->expects($this->once())
            ->method('handle')
            ->willReturn(new GetWorksByAuthorStatusQueryResult([$work]));

        $this->assertEquals('content', $this->widget->render());
    }

    public function testRenderNullAuthor(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
            $this->queryBus
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::SUPERVISOR->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertNull($this->widget->render());
    }

    public function testRenderNoWorks(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->queryBus = $this->createMock(QueryBusInterface::class);
        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
            $this->queryBus
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->queryBus
            ->expects($this->once())
            ->method('handle')
            ->willReturn(new GetWorksByAuthorStatusQueryResult([]));

        $this->assertNull($this->widget->render());
    }

    public function testRenderDeadlines(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->queryBus = $this->createMock(QueryBusInterface::class);
        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
            $this->queryBus
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $work = new Work;
        $work->setDeadline(new DateTimeImmutable('+100 days'));

        $this->queryBus
            ->expects($this->once())
            ->method('handle')
            ->willReturn(new GetWorksByAuthorStatusQueryResult([$work]));

        $this->assertNull($this->widget->render());
    }
}
