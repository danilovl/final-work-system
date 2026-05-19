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

use Doctrine\ORM\{
    NonUniqueResultException,
    NoResultException
};
use Psr\Log\LoggerInterface;
use Stringable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Response
};
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\{
    HttpExceptionInterface,
    MethodNotAllowedHttpException,
    NotFoundHttpException
};
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

readonly class ApiExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private string $kernelEnvironment,
        private LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->kernelEnvironment === 'dev') {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();
        $previous = $exception->getPrevious();

        $this->logger->error($exception);

        $message = match (true) {
            $exception instanceof NotFoundHttpException,
                $exception instanceof NoResultException,
                $exception instanceof NonUniqueResultException,
                $exception instanceof ResourceNotFoundException => 'Resource not found',
            $exception instanceof AccessDeniedException => 'Access denied',
            $exception instanceof MethodNotAllowedHttpException => 'Method not allowed',
            $exception instanceof HttpExceptionInterface => 'Internal Server Error',
            default => 'Unexpected error occurred'
        };

        $statusCode = match (true) {
            $exception instanceof NotFoundHttpException,
                $exception instanceof MethodNotAllowedHttpException => $exception->getStatusCode(),
            $exception instanceof NoResultException,
                $exception instanceof NonUniqueResultException,
                $exception instanceof ResourceNotFoundException => Response::HTTP_NOT_FOUND,
            $exception instanceof AccessDeniedException => Response::HTTP_FORBIDDEN,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            default => Response::HTTP_INTERNAL_SERVER_ERROR
        };

        if ($previous instanceof ValidationFailedException) {
            $message = $this->handleValidationErrors($previous);
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $response = new JsonResponse(
            is_array($message) ? $message : ['message' => $message],
            $statusCode
        );

        $event->setResponse($response);
    }

    /**
     * @return array{message: 'Validation failed', errors: list<array{property: string, message: string|Stringable}>}
     */
    private function handleValidationErrors(ValidationFailedException $exception): array
    {
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $errors[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return ['message' => 'Validation failed', 'errors' => $errors];
    }
}
