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
use App\Domain\Widget\WidgetItem\MenuWidget;
use App\Infrastructure\Service\TwigRenderService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuWidgetTest extends TestCase
{
    private Security $security;

    private MenuWidget $menuWidget;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('trans');

        $router = $this->createStub(RouterInterface::class);
        $router
            ->method('generate')
            ->willReturn('url');

        $userService = $this->createStub(UserService::class);
        $userService
            ->method('getUser')
            ->willReturn(new User);

        $this->security = $this->createStub(Security::class);

        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $this->menuWidget = new MenuWidget(
            $this->getMenu(),
            $translator,
            $router,
            $userService,
            $this->security,
            $twigRenderService
        );
    }

    public function testSetParameters(): void
    {
        $this->expectNotToPerformAssertions();

        $this->menuWidget->setParameters(['menu' => 'main']);
    }

    public function testRenderAccess(): void
    {
        $this->security = $this->createStub(Security::class);
        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $this->menuWidget = new MenuWidget(
            $this->getMenu(),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(RouterInterface::class),
            $this->createStub(UserService::class),
            $this->security,
            $twigRenderService
        );

        $this->security
            ->method('isGranted')
            ->willReturn(true);

        $this->menuWidget->setParameters(['menu' => 'main']);

        $this->assertEquals('content', $this->menuWidget->render());
    }

    public function testRenderNoAccess(): void
    {
        $this->security = $this->createStub(Security::class);
        $twigRenderService = $this->createStub(TwigRenderService::class);
        $twigRenderService
            ->method('render')
            ->willReturn('content');

        $this->menuWidget = new MenuWidget(
            $this->getMenu(),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(RouterInterface::class),
            $this->createStub(UserService::class),
            $this->security,
            $twigRenderService
        );

        $this->security
            ->method('isGranted')
            ->willReturn(false);

        $this->menuWidget->setParameters(['menu' => 'main']);

        $this->assertEquals('content', $this->menuWidget->render());
    }

    private function getMenu(): array
    {
        return [
            'main' => [
                [
                    'label' => 'app.menu.work',
                    'attributes' => ['icon' => 'fa-book'],
                    'roles' => ['ROLE_STUDENT', 'ROLE_OPPONENT', 'ROLE_SUPERVISOR', 'ROLE_CONSULTANT'],
                    'children' => [
                        [
                            'path' => 'work_list',
                            'path_option' => ['type' => 'author'],
                            'label' => 'app.menu.work_author_list',
                            'transchoice' => 2,
                            'attributes' => ['icon' => 'fa-ellipsis-v'],
                            'roles' => ['ROLE_STUDENT']
                        ],
                        [
                            'path' => 'work_list',
                            'path_option' => ['type' => 'opponent'],
                            'label' => 'app.menu.work_opponent_list',
                            'attributes' => ['icon' => 'fa-ellipsis-v'],
                            'roles' => ['ROLE_OPPONENT']
                        ],
                        [
                            'path' => 'work_list',
                            'path_option' => ['type' => 'supervisor'],
                            'label' => 'app.menu.work_opponent_list',
                            'attributes' => ['icon' => 'fa-ellipsis-v'],
                            'roles' => ['ROLE_SUPERVISOR']
                        ],
                        [
                            'path' => 'work_category_list',
                            'label' => 'app.menu.work_category_list',
                            'attributes' => ['icon' => 'fa-ellipsis-v'],
                            'roles' => ['ROLE_SUPERVISOR']
                        ],
                        [
                            'path' => 'work_create',
                            'label' => 'app.menu.work_create',
                            'attributes' => ['icon' => 'fa-pencil'],
                            'roles' => ['ROLE_SUPERVISOR']
                        ]
                    ]
                ]
            ]
        ];
    }
}
