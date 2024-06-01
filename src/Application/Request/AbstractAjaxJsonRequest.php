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

namespace App\Application\Request;

use ReflectionClass;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse,
    RequestStack
};
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\String\s;

abstract class AbstractAjaxJsonRequest
{
    protected array $data = [];
    protected ReflectionClass $reflection;

    public function __construct(
        protected readonly ValidatorInterface $validator,
        protected readonly RequestStack $requestStack
    ) {
        $this->reflection = new ReflectionClass($this);

        $this->populate();
        $this->validate();
    }

    public function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest() ?? new Request;
    }

    protected function populate(): void
    {
        $request = $this->getRequest();

        $this->data = array_merge(
            $request->query->all(),
            $request->request->all()
        );

        $this->fillData($this->data);
    }

    protected function fillData(array $data): void
    {
        foreach ($data as $property => $value) {
            $attribute = self::camelCase($property);
            if (property_exists($this, $attribute)) {
                $reflectionProperty = $this->reflection->getProperty($attribute);
                $reflectionProperty->setValue($this, $value);
            }
        }
    }

    protected function validate(): void
    {
        $errors = [];
        $violations = $this->validator->validate(
            $this->data,
            $this->getConstraints()
        );

        if ($violations->count() === 0) {
            $handleResult = $this->handle($this->getRequest());

            if ($handleResult === false) {
                $this->sendJsonResponse(notifyMessage: $this->getNotifyMessage());
            }

            if ($handleResult === true) {
                return;
            }

            $violations = is_array($handleResult) ? $handleResult : [];
        }

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $attribute = self::snakeCase($violation->getPropertyPath());
            $errors[] = [
                'property' => $attribute,
                'value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];
        }

        if (count($errors) === 0) {
            return;
        }

        $this->sendJsonResponse($errors, $this->getNotifyMessage());
    }

    protected function sendJsonResponse(
        array $errors = [],
        array $notifyMessage = []
    ): void {
        $response = new JsonResponse([
            'valid' => false,
            'errors' => $errors,
            'notifyMessage' => $notifyMessage
        ]);
        $response->send();
        exit;
    }

    private static function camelCase(string $attribute): string
    {
        return s($attribute)->camel()->toString();
    }

    private static function snakeCase(string $attribute): string
    {
        return s($attribute)->snake()->toString();
    }

    abstract protected function getConstraints(): Collection;

    protected function handle(Request $request): array|bool
    {
        return true;
    }

    protected function getNotifyMessage(): array
    {
        return [];
    }
}
