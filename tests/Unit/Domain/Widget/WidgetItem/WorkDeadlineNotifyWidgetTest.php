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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WorkDeadlineNotifyWidgetTest extends TestCase
{
    private MockObject&UserService $userService;

    private MockObject&WorkService $workService;

    private MockObject&WorkFacade $workFacade;

    private WorkDeadlineNotifyWidget $widget;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->workService = $this->createMock(WorkService::class);
        $this->workFacade = $this->createMock(WorkFacade::class);

        $twigRenderService = $this->createMock(TwigRenderService::class);
        $twigRenderService->expects($this->any())
            ->method('render')
            ->willReturn('content');

        $parameterService = $this->createMock(ParameterServiceInterface::class);
        $parameterService->expects($this->any())
            ->method('getString')
            ->willReturn('info');

        $translator = $this->createMock(TranslatorService::class);
        $translator->expects($this->any())
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
        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $work = new Work;
        $work->setDeadline(new DateTime);

        $this->workFacade
            ->expects($this->any())
            ->method('getWorksByAuthorStatus')
            ->willReturn([$work]);

        $this->workService
            ->expects($this->any())
            ->method('getDeadlineDays')
            ->willReturn(10);

        $this->assertEquals('content', $this->widget->render());
    }

    public function testRenderNullAuthor(): void
    {
        $user = new User;
        $user->setRoles([UserRoleConstant::SUPERVISOR->value]);

        $this->userService
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->assertNull($this->widget->render());
    }

    public function testRenderNoWorks(): void
    {
        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->workFacade
            ->expects($this->any())
            ->method('getWorksByAuthorStatus')
            ->willReturn([]);

        $this->assertNull($this->widget->render());
    }

    public function testRenderDeadlines(): void
    {
        $user = new User;
        $user->setRoles([UserRoleConstant::STUDENT->value]);

        $this->userService
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $work = new Work;
        $work->setDeadline(new DateTime);

        $this->workFacade
            ->expects($this->any())
            ->method('getWorksByAuthorStatus')
            ->willReturn([$work]);

        $this->workService
            ->expects($this->any())
            ->method('getDeadlineDays')
            ->willReturn(100);

        $this->assertNull($this->widget->render());
    }
}
