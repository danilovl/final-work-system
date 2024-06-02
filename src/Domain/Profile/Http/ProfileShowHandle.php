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

namespace App\Domain\Profile\Http;

use App\Application\Service\TwigRenderService;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\Response;

readonly class ProfileShowHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService
    ) {}

    public function handle(): Response
    {
        return $this->twigRenderService->renderToResponse('domain/profile/show.html.twig', [
            'user' => $this->userService->getUser()
        ]);
    }
}
