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

namespace App\Domain\Profile\Http\Api;

use App\Infrastructure\Interfaces\Bus\CommandBusInterface;
use App\Domain\Profile\Bus\Command\ProfileChangePassword\ProfileChangePasswordCommand;
use App\Domain\ResetPassword\Form\ResetChangePasswordForm;
use App\Domain\ResetPassword\Model\ResetChangePasswordModel;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Service\TranslatorService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use JsonException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response
};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ProfileChangePasswordHandle
{
    public const string CODE_INVALID_REQUEST = 'invalid_request';

    public const string CODE_MISSING_FIELD = 'missing_field';

    public const string CODE_INVALID_CURRENT_PASSWORD = 'invalid_current_password';

    public const string CODE_INVALID_NEW_PASSWORD = 'invalid_new_password';

    private const string FIELD_CURRENT_PASSWORD = 'currentPassword';

    private const string FIELD_NEW_PASSWORD = 'newPassword';

    private const string TRANSLATION_DOMAIN = 'validators';

    public function __construct(
        private UserService $userService,
        private UserPasswordHasherInterface $userPasswordHasher,
        private FormFactoryInterface $formFactory,
        private ValidatorInterface $validator,
        private TranslatorService $translatorService,
        private ParameterServiceInterface $parameterService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();
        $locale = $this->resolveLocale($request, $user);

        $content = $request->getContent();

        try {
            $payload = json_decode(
                json: $content,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            $payload = null;
        }

        if (!is_array($payload)) {
            $message = $this->trans(
                id: 'app.validator.api_invalid_json',
                parameters: [],
                locale: $locale
            );

            return $this->createErrorResponse(
                code: self::CODE_INVALID_REQUEST,
                message: $message,
                status: Response::HTTP_BAD_REQUEST
            );
        }

        foreach ([self::FIELD_CURRENT_PASSWORD, self::FIELD_NEW_PASSWORD] as $field) {
            $value = $payload[$field] ?? null;
            $isFilled = is_string($value) && $value !== '';
            if (!$isFilled) {
                $message = $this->trans(
                    id: 'app.validator.api_field_required',
                    parameters: ['{{ field }}' => $field],
                    locale: $locale
                );

                return $this->createErrorResponse(
                    code: self::CODE_MISSING_FIELD,
                    message: $message,
                    status: Response::HTTP_BAD_REQUEST
                );
            }
        }

        /** @var string $currentPassword */
        $currentPassword = $payload[self::FIELD_CURRENT_PASSWORD];
        /** @var string $newPassword */
        $newPassword = $payload[self::FIELD_NEW_PASSWORD];

        $isCurrentPasswordValid = $this->userPasswordHasher->isPasswordValid($user, $currentPassword);
        if (!$isCurrentPasswordValid) {
            $message = $this->trans(
                id: 'app.validator.current_password_invalid',
                parameters: [],
                locale: $locale
            );

            return $this->createErrorResponse(
                code: self::CODE_INVALID_CURRENT_PASSWORD,
                message: $message,
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $newPasswordError = $this->validateNewPassword($newPassword, $locale);
        if ($newPasswordError !== null) {
            return $this->createErrorResponse(
                code: self::CODE_INVALID_NEW_PASSWORD,
                message: $newPasswordError,
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $command = ProfileChangePasswordCommand::create(
            user: $user,
            plainPassword: $newPassword
        );
        $this->commandBus->dispatch($command);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    private function validateNewPassword(string $newPassword, string $locale): ?string
    {
        $form = $this->formFactory->create(
            type: ResetChangePasswordForm::class,
            data: new ResetChangePasswordModel,
            options: ['csrf_protection' => false]
        );

        /** @var Constraint[] $constraints */
        $constraints = $form->get('plainPassword')
            ->get('first')
            ->getConfig()
            ->getOption('constraints', []);

        $violations = $this->validator->validate(
            value: $newPassword,
            constraints: $constraints
        );
        if ($violations->count() === 0) {
            return null;
        }

        $violation = $violations->get(0);
        $parameters = $violation->getParameters();

        $plural = $violation->getPlural();
        if ($plural !== null) {
            $parameters['%count%'] = $plural;
        }

        $messageTemplate = $violation->getMessageTemplate();

        return $this->trans(
            id: $messageTemplate,
            parameters: $parameters,
            locale: $locale
        );
    }

    private function resolveLocale(Request $request, User $user): string
    {
        $localesParameter = $this->parameterService->getString('locales');
        $locales = explode('|', $localesParameter);

        $hasAcceptLanguage = $request->headers->has('Accept-Language');
        if ($hasAcceptLanguage) {
            $preferredLanguage = $request->getPreferredLanguage($locales);
            if ($preferredLanguage !== null) {
                return $preferredLanguage;
            }
        }

        $userLocale = $user->getLocale();
        $isUserLocaleSupported = $userLocale !== null && in_array($userLocale, $locales, true);
        if ($isUserLocaleSupported) {
            return $userLocale;
        }

        return $request->getLocale();
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function trans(string $id, array $parameters, string $locale): string
    {
        return $this->translatorService->trans(
            id: $id,
            parameters: $parameters,
            domain: self::TRANSLATION_DOMAIN,
            locale: $locale
        );
    }

    private function createErrorResponse(string $code, string $message, int $status): JsonResponse
    {
        return new JsonResponse(
            data: [
                'code' => $code,
                'message' => $message
            ],
            status: $status
        );
    }
}
