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

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\WorkDeadlineNotifyWidget;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\Work\Service\WorkService;
use App\Infrastructure\Service\{
    TranslatorService
};
use App\Infrastructure\Service\TwigRenderService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTime;
use PHPUnit\Framework\TestCase;

class WorkDeadlineNotifyWidgetTest extends TestCase
{
    private UserService $userService;

    private WorkService $workService;

    private WorkFacade $workFacade;

    private WorkDeadlineNotifyWidget $widget;

    protected function setUp(): void
    {
        $this->userService = $this->createStub(UserService::class);
        $this->workService = $this->createStub(WorkService::class);
        $this->workFacade = $this->createStub(WorkFacade::class);

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
            $this->workService,
            $parameterService,
            $translator,
            $twigRenderService,
            $this->workFacade
        );
    }

    public function testRender(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->workFacade = $this->createMock(WorkFacade::class);
        $this->workService = $this->createMock(WorkService::class);
        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->workService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $twigRenderService,
            $this->workFacade
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $work = new Work;
        $work->setDeadline(new DateTime);

        $this->workFacade
            ->expects($this->once())
            ->method('listByAuthorStatus')
            ->willReturn([$work]);

        $this->workService
            ->expects($this->once())
            ->method('getDeadlineDays')
            ->willReturn(10);

        $this->assertEquals('content', $this->widget->render());
    }

    public function testRenderNullAuthor(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->workService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
            $this->workFacade
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
        $this->workFacade = $this->createMock(WorkFacade::class);
        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->createStub(WorkService::class),
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
            $this->workFacade
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->workFacade
            ->expects($this->once())
            ->method('listByAuthorStatus')
            ->willReturn([]);

        $this->assertNull($this->widget->render());
    }

    public function testRenderDeadlines(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->workFacade = $this->createMock(WorkFacade::class);
        $this->workService = $this->createMock(WorkService::class);
        $this->widget = new WorkDeadlineNotifyWidget(
            $this->userService,
            $this->workService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
            $this->workFacade
        );

        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $work = new Work;
        $work->setDeadline(new DateTime);

        $this->workFacade
            ->expects($this->once())
            ->method('listByAuthorStatus')
            ->willReturn([$work]);

        $this->workService
            ->expects($this->once())
            ->method('getDeadlineDays')
            ->willReturn(100);

        $this->assertNull($this->widget->render());
    }
}
