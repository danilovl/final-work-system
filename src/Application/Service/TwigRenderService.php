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

namespace App\Application\Service;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Twig\Environment;

class TwigRenderService
{
    public function __construct(
        private Environment $environment,
        private ParameterServiceInterface $parameterService
    ) {
    }

    public function render(
        string $view,
        array $parameters = [],
        Response $response = null
    ): Response {
        $content = $this->environment->render($view, $parameters);
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
}
