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

namespace App\Domain\Widget\WidgetItem;

use App\Application\Menu\MenuItem;
use App\Infrastructure\Service\TwigRenderService;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuWidget extends BaseWidget
{
    private ?array $factory = null;

    private ?User $user;

    private string $menu;

    public function __construct(
        private readonly array $menuConfig,
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
        private readonly UserService $userService,
        private readonly Security $security,
        private readonly TwigRenderService $twigRenderService
    ) {
        $this->user = $this->userService->getUser();
    }

    private function build(): void
    {
        foreach ($this->menuConfig as $name => $item) {
            $this->factory[$name] = $this->buildMenu($item);
        }
    }

    private function buildMenu(array $items): array
    {
        $subMenu = [];

        foreach ($items as $item) {
            $roles = $item['roles'] ?? null;

            $access = $roles === null;
            if ($roles !== null) {
                foreach ($roles as $role) {
                    $access = $this->security->isGranted($role, $this->user);
                    if ($access === true) {
                        break;
                    }
                }
            }

            if (!$access) {
                continue;
            }

            $name = $item['label'] ?? 'notLabel';
            $menu = new MenuItem($name);

            $label = $item['label'] ?? null;
            if ($label !== null) {
                $transParameters = [];
                if (isset($item['transchoice'])) {
                    $transParameters = ['%count%' => $item['transchoice']];
                }

                $menu->setLabel($this->translator->trans($item['label'], $transParameters));
            }

            $path = $item['path'] ?? null;
            $pathOption = $item['path_option'] ?? [];
            if ($path !== null) {
                $menu->setUri($this->router->generate($path, $pathOption));
            }

            $attributes = $item['attributes'] ?? null;
            if ($attributes !== null) {
                $menu->setAttributes($attributes);
            }

            $children = $item['children'] ?? null;
            if ($children !== null) {
                $menu->setChildren($this->buildMenu($children));
            }

            $subMenu[] = $menu;
        }

        return $subMenu;
    }

    /**
     * @param array{menu: string} $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->menu = $parameters['menu'];
    }

    public function getRenderParameters(): array
    {
        return [
            'menu' => $this->getMenuConfig($this->menu)
        ];
    }

    public function getMenuConfig(string $menu): ?array
    {
        if ($this->factory === null) {
            $this->build();
        }

        return $this->factory[$menu] ?? null;
    }

    public function render(): string
    {
        return $this->twigRenderService->render("application/widget/menu/{$this->menu}.html.twig", $this->getRenderParameters());
    }
}
