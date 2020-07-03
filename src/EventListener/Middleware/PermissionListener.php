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

namespace App\EventListener\Middleware;

use DateTime;
use App\Annotation\PermissionMiddleware;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\{
    NotFoundHttpException,
    AccessDeniedHttpException
};
use Symfony\Component\Security\Core\Security;

class PermissionListener
{
    private Reader $reader;
    private Security $security;

    public function __construct(
        Reader $reader,
        Security $security
    ) {
        $this->reader = $reader;
        $this->security = $security;
    }

    public function onKernelController(ControllerEvent $event)
    {
        if (!is_array($controllers = $event->getController())) {
            return;
        }

        list($controller, $methodName) = $controllers;

        $reflectionClass = new ReflectionClass($controller);
        /** @var PermissionMiddleware $classPermissionMiddleware */
        $classPermissionMiddleware = $this->reader
            ->getClassAnnotation($reflectionClass, PermissionMiddleware::class);

        if ($classPermissionMiddleware !== null) {
            $this->checkPermission($classPermissionMiddleware);
        }

        $reflectionObject = new ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        /** @var PermissionMiddleware $methodPermissionMiddleware */
        $methodPermissionMiddleware = $this->reader
            ->getMethodAnnotation($reflectionMethod, PermissionMiddleware::class);

        if ($methodPermissionMiddleware !== null) {
            $this->checkPermission($methodPermissionMiddleware);
        }

        return;
    }

    private function checkPermission(PermissionMiddleware $permissionMiddleware): void
    {
        $user = $this->security->getUser();

        $roles = $permissionMiddleware->roles;
        if ($roles) {
            if ($user === null) {
                throw new NotFoundHttpException;
            }

            $isGranted = false;
            foreach ($roles as $role) {
                if ($this->security->isGranted($role, $user)) {
                    $isGranted = true;
                    break;
                }
            }

            if (!$isGranted) {
                throw new AccessDeniedHttpException;
            }
        }

        $users = $permissionMiddleware->users;
        if ($users) {
            if ($user === null) {
                throw new NotFoundHttpException;
            }

            if (!in_array($user->getUsername(), $users)) {
                throw new AccessDeniedHttpException;
            }
        }

        $dateFrom = $permissionMiddleware->dateFrom;
        if ($dateFrom) {
            if (new DateTime('now') < $dateFrom) {
                throw new AccessDeniedHttpException;
            }
        }

        $dateTo = $permissionMiddleware->dateTo;
        if ($dateTo) {
            if (new DateTime('now') > $dateTo) {
                throw new AccessDeniedHttpException;
            }
        }
    }
}