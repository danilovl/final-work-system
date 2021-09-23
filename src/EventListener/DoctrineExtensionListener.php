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

namespace App\EventListener;

use App\Service\UserService;
use Gedmo\Loggable\LoggableListener;

class DoctrineExtensionListener
{
    public function __construct(
        private UserService $userService,
        private LoggableListener $loggableListener
    ) {
    }

    public function onKernelRequest(): void
    {
        $user = $this->userService->getUser();
        if ($user === null) {
            return;
        }

        $this->loggableListener->setUsername($user->getUsername());
    }
}