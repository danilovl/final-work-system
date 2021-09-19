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

namespace App\Service;

use App\Constant\{
    FlashTypeConstant,
    AjaxJsonTypeConstant
};
use App\Exception\ConstantNotFoundException;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse,
    RequestStack,
    RedirectResponse
};
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class RequestService
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router,
        private TranslatorService $translatorService
    ) {
    }

    public function addFlash(string $type, mixed $message): void
    {
        $this->requestStack->getSession()->getFlashBag()->add($type, $message);
    }

    public function addFlashTrans(string $type, mixed $message): void
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
        string $type,
        ?array $extraData = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        $data = match ($type) {
            AjaxJsonTypeConstant::CREATE_SUCCESS => [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS => $this->translatorService->trans('app.flash.form.create.success')
                ]
            ],
            AjaxJsonTypeConstant::CREATE_FAILURE => [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR => $this->translatorService->trans('app.flash.form.create.error'),
                    FlashTypeConstant::WARNING => $this->translatorService->trans('app.flash.form.create.warning')
                ],
            ],
            AjaxJsonTypeConstant::SAVE_SUCCESS => [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS => $this->translatorService->trans('app.flash.form.save.success'),
                ]
            ],
            AjaxJsonTypeConstant::SAVE_FAILURE => [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR => $this->translatorService->trans('app.flash.form.save.error'),
                    FlashTypeConstant::WARNING => $this->translatorService->trans('app.flash.form.save.warning')
                ]
            ],
            AjaxJsonTypeConstant::DELETE_SUCCESS => [
                'delete' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS => $this->translatorService->trans('app.flash.form.delete.success')
                ]
            ],
            AjaxJsonTypeConstant::DELETE_FAILURE => [
                'delete' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR => $this->translatorService->trans('app.flash.form.delete.error'),
                    FlashTypeConstant::WARNING => $this->translatorService->trans('app.flash.form.delete.warning')
                ]
            ],
            default => throw new ConstantNotFoundException('AjaxJson constant type not found'),
        };

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return new JsonResponse($data, $statusCode);
    }
}
