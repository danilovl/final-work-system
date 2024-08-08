<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Controller;

use App\Application\Service\TwigRenderService;
use Symfony\Component\HttpFoundation\Response;

readonly class DefaultController
{
    public function __construct(private TwigRenderService $twigRenderService) {}

    public function index(): Response
    {
        return $this->twigRenderService->renderToResponse('base.html.twig');
    }
}
