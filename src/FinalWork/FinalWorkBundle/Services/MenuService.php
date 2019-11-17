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

namespace FinalWork\FinalWorkBundle\Services;

use FinalWork\FinalWorkBundle\Menu\MenuItem;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};
use Twig\Extension\RuntimeExtensionInterface;

class MenuService implements RuntimeExtensionInterface
{
    /**
     * @var array
     */
    private $factory;

    /**
     * @var array
     */
    private $menu;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * MenuService constructor.
     * @param array $menu
     * @param TranslatorInterface $dataCollectorTranslator
     * @param Router $router
     * @param Security $security
     * @param Environment $environment
     */
    public function __construct(
        array $menu,
        TranslatorInterface $dataCollectorTranslator,
        Router $router,
        Security $security,
        Environment $environment
    ) {
        $this->menu = $menu;
        $this->translator = $dataCollectorTranslator;
        $this->router = $router;
        $this->security = $security;
        $this->environment = $environment;
        $this->user = $this->security->getUser();
    }

    /**
     * @return void
     */
    private function build(): void
    {
        foreach ($this->menu as $name => $item) {
            $this->factory[$name] = $this->buildMenu($item);
        }
    }

    /**
     * @param array $items
     * @return array
     */
    private function buildMenu(array $items): array
    {
        $subMenu = [];

        foreach ($items as $item) {
            $roles = $item['roles'] ?? null;
            if ($roles !== null && !$this->security->isGranted($roles, $this->user)) {
                continue;
            }

            $name = $item['label'] ?? 'notLabel';
            $menu = new MenuItem($name);

            $label = $item['label'] ?? null;
            if ($label !== null) {
                $transParameters = [];
                if (isset($item['transchoice'])) {
                    $menu->setLabel($this->translator->transChoice($item['label'], $item['transchoice'], $transParameters));
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

    /**
     * @param string $menu
     * @return array|null
     */
    public function getMenu(string $menu): ?array
    {
        if ($this->factory === null) {
            $this->build();
        }

        return $this->factory[$menu] ?? null;
    }

    /**
     * @param string $menu
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderMenu(string $menu): string
    {
        return $this->environment
            ->render("@FinalWork/menu/{$menu}.html.twig", [
                'menu' => $this->getMenu($menu)
            ]);
    }
}
