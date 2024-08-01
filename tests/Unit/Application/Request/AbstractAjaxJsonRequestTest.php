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

namespace App\Tests\Unit\Application\Request;

use App\Application\Request\AbstractAjaxJsonRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    RequestStack
};
use Symfony\Component\Validator\Constraints\{
    NotBlank,
    Collection
};
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractAjaxJsonRequestTest extends TestCase
{
    private readonly RequestStack $requestStackMissing;
    private readonly RequestStack $requestStackFilled;

    protected function setUp(): void
    {
        $request = new Request;
        $request->query->set('start', '2020-01-01');
        $request->query->set('end', '');

        $requestStack = new RequestStack;
        $requestStack->push($request);

        $this->requestStackMissing = $requestStack;

        $request = new Request;
        $request->query->set('start', '2020-01-01');
        $request->query->set('end', '2020-01-01');

        $requestStack = new RequestStack;
        $requestStack->push($request);

        $this->requestStackFilled = $requestStack;
    }

    public function testHandleMissing(): void
    {
        $jsonResponse = $this->createMock(JsonResponse::class);
        $jsonResponse->expects($this->once())
            ->method('send');

        new class ($jsonResponse, Validation::createValidator(), $this->requestStackMissing) extends AbstractAjaxJsonRequest {
            public string $start;
            public string $end;

            public function __construct(
                private readonly JsonResponse $jsonResponse,
                ValidatorInterface $validator,
                RequestStack $requestStack
            ) {
                parent::__construct($validator, $requestStack);
            }

            protected function getConstraints(): Collection
            {
                return new Collection([
                    'start' => [new NotBlank],
                    'end' => [new NotBlank]
                ]);
            }

            protected function sendJsonResponse(
                array $errors = [],
                array $notifyMessage = [],
                bool $send = true
            ): void {
                parent::sendJsonResponse($errors, $notifyMessage);
            }

            protected function createJsonResponse(
                bool $valid,
                array $errors = [],
                array $notifyMessage = [],
            ): JsonResponse {
                return $this->jsonResponse;
            }
        };

        $this->assertTrue(true);
    }

    public function testHandleFalse(): void
    {
        new class (Validation::createValidator(), $this->requestStackFilled) extends AbstractAjaxJsonRequest {
            public string $start;
            public string $end;

            protected function getConstraints(): Collection
            {
                return new Collection([
                    'start' => [new NotBlank],
                    'end' => [new NotBlank]
                ]);
            }

            protected function handle(Request $request): array|bool
            {
                return false;
            }

            protected function sendJsonResponse(
                array $errors = [],
                array $notifyMessage = [],
                bool $send = true
            ): void {
                parent::sendJsonResponse($errors, $notifyMessage, false);
            }
        };

        $this->assertTrue(true);
    }

    public function testHandleTrue(): void
    {
        new class (Validation::createValidator(), $this->requestStackFilled) extends AbstractAjaxJsonRequest {
            public string $start;
            public string $end;

            protected function getConstraints(): Collection
            {
                return new Collection([
                    'start' => [new NotBlank],
                    'end' => [new NotBlank]
                ]);
            }

            protected function sendJsonResponse(
                array $errors = [],
                array $notifyMessage = [],
                bool $send = true
            ): void {
                parent::sendJsonResponse($errors, $notifyMessage, false);
            }
        };

        $this->assertTrue(true);
    }

    public function testHandleArray(): void
    {
        new class (Validation::createValidator(), $this->requestStackFilled) extends AbstractAjaxJsonRequest {
            public string $start;
            public string $end;

            protected function getConstraints(): Collection
            {
                return new Collection([
                    'start' => [new NotBlank],
                    'end' => [new NotBlank]
                ]);
            }

            protected function handle(Request $request): array|bool
            {
                return [];
            }

            protected function sendJsonResponse(
                array $errors = [],
                array $notifyMessage = [],
                bool $send = true
            ): void {
                parent::sendJsonResponse($errors, $notifyMessage, false);
            }
        };

        $this->assertTrue(true);
    }
}
