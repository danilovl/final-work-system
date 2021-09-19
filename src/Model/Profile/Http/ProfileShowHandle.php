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

namespace App\Model\Profile\Http;

use App\Service\{
    UserService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\Response;

class ProfileShowHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService
    ) {
    }

    public function handle(): Response
    {
        return $this->twigRenderService->render('profile/show.html.twig', [
            'user' => $this->userService->getUser()
        ]);
    }
}
