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

namespace App\Widget;

use App\Entity\User;
use App\Menu\MenuItem;
use App\Services\UserService;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MenuWidget extends BaseWidget
{
    private ?array $factory = null;
    private array $menuConfig;
    private Router $router;
    private TranslatorInterface $translator;
    private ?User $user;
    private UserService $userService;
    private Environment $environment;
    private Security $security;

    private ?string $menu;

    public function __construct(
        array $menu,
        TranslatorInterface $dataCollectorTranslator,
        Router $router,
        UserService $userService,
        Security $security,
        Environment $environment
    ) {
        $this->menuConfig = $menu;
        $this->translator = $dataCollectorTranslator;
        $this->router = $router;
        $this->userService = $userService;
        $this->security = $security;
        $this->environment = $environment;
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

            $access = $roles !== null ? false : true;
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
                    $menu->setLabel($this->translator->trans($item['label'], $transParameters));
                } else {
                    $menu->setLabel($this->translator->trans($item['label'], $transParameters));
                }
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

    public function setParameters(array $parameters): void
    {
        $this->menu = $parameters['menu'] ?? null;
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
        return $this->environment->render("widget/menu/{$this->menu}.html.twig", $this->getRenderParameters());
    }
}