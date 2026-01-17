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

use App\Application\Constant\{
    AjaxJsonTypeConstant,
    FlashTypeConstant
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    RedirectResponse,
    RequestStack,
    Response
};
use Symfony\Component\HttpFoundation\Session\{
    Session,
    SessionInterface
};
use Symfony\Component\Routing\RouterInterface;

readonly class RequestService
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router,
        private TranslatorService $translatorService
    ) {}

    public function addFlash(string $type, mixed $message): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add($type, $message);
    }

    public function addFlashTrans(string $type, string $message): void
    {
        $this->addFlash(
            $type,
            $this->translatorService->trans($message)
        );
    }

    public function redirectToRoute(
        string $route,
        array $parameters = [],
        int $status = Response::HTTP_FOUND
    ): RedirectResponse {
        return new RedirectResponse(
            $this->router->generate($route, $parameters),
            $status
        );
    }

    public function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function createAjaxJson(
        AjaxJsonTypeConstant $type,
        ?array $extraData = null,
        ?int $statusCode = null
    ): JsonResponse {
        $data = match ($type) {
            AjaxJsonTypeConstant::CREATE_SUCCESS => [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => $this->translatorService->trans('app.flash.form.create.success')
                ]
            ],
            AjaxJsonTypeConstant::CREATE_FAILURE => [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => $this->translatorService->trans('app.flash.form.create.error'),
                    FlashTypeConstant::WARNING->value => $this->translatorService->trans('app.flash.form.create.warning')
                ],
            ],
            AjaxJsonTypeConstant::SAVE_SUCCESS => [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => $this->translatorService->trans('app.flash.form.save.success'),
                ]
            ],
            AjaxJsonTypeConstant::SAVE_FAILURE => [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => $this->translatorService->trans('app.flash.form.save.error'),
                    FlashTypeConstant::WARNING->value => $this->translatorService->trans('app.flash.form.save.warning')
                ]
            ],
            AjaxJsonTypeConstant::DELETE_SUCCESS => [
                'delete' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => $this->translatorService->trans('app.flash.form.delete.success')
                ]
            ],
            AjaxJsonTypeConstant::DELETE_FAILURE => [
                'delete' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => $this->translatorService->trans('app.flash.form.delete.error'),
                    FlashTypeConstant::WARNING->value => $this->translatorService->trans('app.flash.form.delete.warning')
                ]
            ]
        };

        if ($statusCode === null) {
            $statusCode = match ($type) {
                AjaxJsonTypeConstant::CREATE_SUCCESS => Response::HTTP_CREATED,
                AjaxJsonTypeConstant::SAVE_SUCCESS, AjaxJsonTypeConstant::DELETE_SUCCESS => Response::HTTP_OK,
                AjaxJsonTypeConstant::CREATE_FAILURE,
                AjaxJsonTypeConstant::SAVE_FAILURE,
                AjaxJsonTypeConstant::DELETE_FAILURE => Response::HTTP_BAD_REQUEST
            };
        }

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return new JsonResponse($data, $statusCode);
    }
}
