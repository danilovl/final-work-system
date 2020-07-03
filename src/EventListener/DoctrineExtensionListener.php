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

use App\Entity\User;
use Gedmo\Loggable\LoggableListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class DoctrineExtensionListener
{
    private Security $security;
    private LoggableListener $loggableListener;

    public function __construct(
        Security $security,
        LoggableListener $loggableListener
    ) {
        $this->security = $security;
        $this->loggableListener = $loggableListener;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user !== null) {
            $this->loggableListener->setUsername($user->getUsername());
        }
    }
}