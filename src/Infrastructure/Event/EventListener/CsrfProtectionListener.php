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

namespace App\Infrastructure\Event\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\{
    CsrfToken,
    CsrfTokenManagerInterface
};

readonly class CsrfProtectionListener implements EventSubscriberInterface
{
    final public const string TOKEN_ID = 'ajax';

    final public const string HEADER_NAME = 'X-CSRF-Token';

    final public const string FIELD_NAME = '_csrf_token';

    private const string AJAX_ROUTE_SUFFIX = '_ajax';

    private const array PROTECTED_ROUTES = [
        'task_delete',
        'work_category_delete',
        'document_category_delete'
    ];

    public function __construct(private CsrfTokenManagerInterface $csrfTokenManager) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->isMethodSafe()) {
            return;
        }

        $route = $request->attributes->get('_route');
        if (!is_string($route) || !$this->isProtectedRoute($route)) {
            return;
        }

        $token = $request->headers->get(self::HEADER_NAME) ?? $request->request->get(self::FIELD_NAME);
        $tokenValue = is_string($token) ? $token : null;
        $csrfToken = new CsrfToken(self::TOKEN_ID, $tokenValue);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }
    }

    private function isProtectedRoute(string $route): bool
    {
        if (str_ends_with($route, self::AJAX_ROUTE_SUFFIX)) {
            return true;
        }

        return in_array($route, self::PROTECTED_ROUTES, true);
    }
}
