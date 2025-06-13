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

use App\Domain\Widget\WidgetItem\UserProfileInformationNotifyWidget;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\Service\{
    TranslatorService,
    TwigRenderService
};
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class UserProfileInformationNotifyWidgetTest extends TestCase
{
    private MockObject&UserService $userService;

    private UserProfileInformationNotifyWidget $widget;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);

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

        $this->widget = new UserProfileInformationNotifyWidget(
            $this->userService,
            $parameterService,
            $translator,
            $twigRenderService,
        );
    }

    public function testRenderNoEmptyPhoneSkype(): void
    {
        $user = new User;
        $user->setPhone('1111111111');
        $user->setSkype('skype');

        $this->userService->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals('', $this->widget->render());
    }

    public function testRender(): void
    {
        $user = new User;
        $user->setPhone(null);
        $user->setSkype(null);

        $this->userService->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals('contentcontent', $this->widget->render());
    }
}
