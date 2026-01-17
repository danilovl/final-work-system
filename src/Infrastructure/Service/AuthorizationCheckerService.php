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

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

readonly class AuthorizationCheckerService
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker) {}

    public function denyAccessUnlessGranted(
        mixed $attribute,
        mixed $subject = null,
        string $message = 'Access Denied.'
    ): void {
        if ($this->isGranted($attribute, $subject)) {
            return;
        }

        $exception = $this->createAccessDeniedException($message);
        $exception->setAttributes([$attribute]);
        $exception->setSubject($subject);

        throw $exception;
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    private function createAccessDeniedException(
        string $message = 'Access Denied.',
        ?Throwable $previous = null
    ): AccessDeniedException {
        return new AccessDeniedException($message, $previous);
    }
}
