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

use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\Widget\WidgetItem\UserProfileInformationNotifyWidget;
use App\Infrastructure\Service\{
    TranslatorService
};
use App\Infrastructure\Service\TwigRenderService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class UserProfileInformationNotifyWidgetTest extends TestCase
{
    private UserService $userService;

    private UserProfileInformationNotifyWidget $widget;

    protected function setUp(): void
    {
        $this->userService = $this->createStub(UserService::class);

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

        $this->widget = new UserProfileInformationNotifyWidget(
            $this->userService,
            $parameterService,
            $translator,
            $twigRenderService,
        );
    }

    public function testRenderNoEmptyPhoneSkype(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->widget = new UserProfileInformationNotifyWidget(
            $this->userService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $this->createStub(TwigRenderService::class),
        );

        $user = new User;
        $user->setPhone('1111111111');
        $user->setSkype('skype');

        $this->userService
            ->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals('', $this->widget->render());
    }

    public function testRender(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $this->widget = new UserProfileInformationNotifyWidget(
            $this->userService,
            $this->createStub(ParameterServiceInterface::class),
            $this->createStub(TranslatorService::class),
            $twigRenderService,
        );

        $user = new User;
        $user->setPhone(null);
        $user->setSkype(null);

        $this->userService
            ->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals('contentcontent', $this->widget->render());
    }
}
