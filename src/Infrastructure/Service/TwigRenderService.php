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

namespace App\Infrastructure\Service;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Twig\Environment;
use Twig\Loader\LoaderInterface;

readonly class TwigRenderService
{
    public function __construct(
        private Environment $twig,
        private ParameterServiceInterface $parameterService
    ) {}

    public function render(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    public function renderToResponse(
        string $view,
        array $parameters = [],
        ?Response $response = null
    ): Response {
        $content = $this->twig->render($view, $parameters);
        if ($response === null) {
            $response = new Response;
        }

        $response->setContent($content);

        return $response;
    }

    public function ajaxOrNormalFolder(Request $request, string $templateName): string
    {
        $file = basename($templateName);
        $fileAjax = sprintf('%s%s', $this->parameterService->getString('template.ajax'), $file);
        $templateNameAjax = str_replace($file, $fileAjax, $templateName);

        if ($request->isXmlHttpRequest()) {
            return $templateNameAjax;
        }

        return $templateName;
    }

    public function getLoader(): LoaderInterface
    {
        return $this->twig->getLoader();
    }
}
