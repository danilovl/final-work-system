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

namespace FinalWork\FinalWorkBundle\Twig;

use Twig\TwigFunction;
use FinalWork\FinalWorkBundle\Services\{
    MenuService,
    SystemEventLinkGeneratorService
};
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ChangeLanguageExtension constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('systemEventGenerateLink', [SystemEventLinkGeneratorService::class, 'generateLink']),
            new TwigFunction('renderMenu', [MenuService::class, 'renderMenu'], ['is_safe' => ['html']]),
            new TwigFunction('renderServiceMethod', [$this, 'renderServiceMethod'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $service
     * @param string $method
     * @param array $parameters
     * @return string
     */
    public function renderServiceMethod(
        string $service,
        string $method,
        array $parameters = []
    ): string {
        $service = $this->container->get($service);

        /** @var Response $response */
        $response = call_user_func_array([$service, $method], $parameters);

        return $response->getContent();
    }
}
