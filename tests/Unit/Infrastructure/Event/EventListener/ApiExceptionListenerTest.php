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

namespace App\Tests\Unit\Infrastructure\Event\EventListener;

use App\Infrastructure\Event\EventListener\ApiExceptionListener;
use Doctrine\ORM\{
    NoResultException,
    NonUniqueResultException
};
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\{
    NotFoundHttpException,
    MethodNotAllowedHttpException
};
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\{
    ConstraintViolation,
    ConstraintViolationList
};
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiExceptionListenerTest extends TestCase
{
    private ApiExceptionListener $listener;

    private MockObject&HttpKernelInterface $kernel;

    private Request $request;

    private MockObject&LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->request = new Request;
        $this->request->server->set('REQUEST_URI', '/api/test');
        $this->listener = new ApiExceptionListener('prod', $this->logger);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = ApiExceptionListener::getSubscribedEvents();

        $this->assertArrayHasKey(ExceptionEvent::class, $subscribedEvents);
        $this->assertEquals('onKernelException', $subscribedEvents[ExceptionEvent::class]);
    }

    public function testDevEnvironment(): void
    {
        $listener = new ApiExceptionListener('dev', $this->logger);
        $exception = new Exception('Test exception');

        $this->logger
            ->expects($this->never())
            ->method('error');

        $event = new ExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testNonApiRoute(): void
    {
        $request = new Request;
        $request->server->set('REQUEST_URI', '/non-api/route');

        $exception = new Exception('Test exception');

        $this->logger
            ->expects($this->never())
            ->method('error');

        $event = new ExceptionEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testNotFoundExceptions(): void
    {
        $exceptions = [
            new NotFoundHttpException('Not found'),
            new NoResultException,
            new NonUniqueResultException('Non unique result'),
            new ResourceNotFoundException('Resource not found')
        ];

        $this->logger
            ->expects($this->exactly(count($exceptions)))
            ->method('error');

        foreach ($exceptions as $exception) {

            $event = new ExceptionEvent(
                $this->kernel,
                $this->request,
                HttpKernelInterface::MAIN_REQUEST,
                $exception
            );

            $this->listener->onKernelException($event);

            /** @var Response $response */
            $response = $event->getResponse();
            /** @var string $responseContent */
            $responseContent = $response->getContent();
            /** @var array $content */
            $content = json_decode($responseContent, true);

            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
            $this->assertEquals('Resource not found', $content['message']);
        }
    }

    public function testAccessDeniedException(): void
    {
        $exception = new AccessDeniedException('Access denied');

        $this->logger
            ->expects($this->once())
            ->method('error');

        $event = new ExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->onKernelException($event);

        /** @var Response $response */
        $response = $event->getResponse();
        /** @var string $responseContent */
        $responseContent = $response->getContent();
        /** @var array $content */
        $content = json_decode($responseContent, true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals('Access denied', $content['message']);
    }

    public function testMethodNotAllowedException(): void
    {
        $exception = new MethodNotAllowedHttpException(['GET'], 'Method not allowed');

        $this->logger
            ->expects($this->once())
            ->method('error');

        $event = new ExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->onKernelException($event);

        /** @var Response $response */
        $response = $event->getResponse();
        /** @var string $responseContent */
        $responseContent = $response->getContent();
        /** @var array $content */
        $content = json_decode($responseContent, true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertEquals('Method not allowed', $content['message']);
    }

    public function testValidationException(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Error 1', null, [], null, 'property1', null),
            new ConstraintViolation('Error 2', null, [], null, 'property2', null)
        ]);

        $validationException = new ValidationFailedException('Validation failed', $violations);
        $exception = new Exception('Wrapper exception', 0, $validationException);

        $this->logger
            ->expects($this->once())
            ->method('error');

        $event = new ExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->onKernelException($event);

        /** @var Response $response */
        $response = $event->getResponse();
        /** @var string $responseContent */
        $responseContent = $response->getContent();
        /** @var array $content */
        $content = json_decode($responseContent, true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('Validation failed', $content['message']);
        $this->assertCount(2, $content['errors']);
        $this->assertEquals('property1', $content['errors'][0]['property']);
        $this->assertEquals('Error 1', $content['errors'][0]['message']);
        $this->assertEquals('property2', $content['errors'][1]['property']);
        $this->assertEquals('Error 2', $content['errors'][1]['message']);
    }

    public function testGenericException(): void
    {
        $exception = new Exception('Generic exception');

        $this->logger
            ->expects($this->once())
            ->method('error');

        $event = new ExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->listener->onKernelException($event);

        /** @var Response $response */
        $response = $event->getResponse();
        /** @var string $responseContent */
        $responseContent = $response->getContent();
        /** @var array $content */
        $content = json_decode($responseContent, true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('Unexpected error occurred', $content['message']);
    }
}
