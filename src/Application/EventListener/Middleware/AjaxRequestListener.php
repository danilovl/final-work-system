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

namespace App\Application\EventListener\Middleware;

use App\Application\Attribute\AjaxRequestMiddlewareAttribute;
use App\Application\Constant\FlashTypeConstant;
use App\Application\Exception\AjaxRuntimeException;
use App\Application\Service\TranslatorService;
use ReflectionClass;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class AjaxRequestListener
{
    public function __construct(private TranslatorService $translator)
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!is_array($controllers = $event->getController())) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getMethod() !== Request::METHOD_POST) {
            return;
        }

        [$controller, $method] = $controllers;

        $this->controllerMiddleware($controller, $event, $request);
        $this->methodMiddleware($controller, $method, $event, $request);
    }

    private function controllerMiddleware(
        object $controller,
        ControllerEvent $event,
        Request $request
    ): void {
        $attributes = (new ReflectionClass($controller))->getAttributes(AjaxRequestMiddlewareAttribute::class);

        foreach ($attributes as $attribute) {
            /** @var AjaxRequestMiddlewareAttribute $ajaxRequestMiddlewareAttribute */
            $ajaxRequestMiddlewareAttribute = $attribute->newInstance();
            $this->handleRequest($event, $ajaxRequestMiddlewareAttribute, $request);
        }
    }

    private function methodMiddleware(
        object $controller,
        string $method,
        ControllerEvent $event,
        Request $request
    ): void {
        $attributes = (new ReflectionClass($controller))
            ->getMethod($method)
            ->getAttributes(AjaxRequestMiddlewareAttribute::class);

        foreach ($attributes as $attribute) {
            /** @var AjaxRequestMiddlewareAttribute $ajaxRequestMiddlewareAttribute */
            $ajaxRequestMiddlewareAttribute = $attribute->newInstance();
            $this->handleRequest($event, $ajaxRequestMiddlewareAttribute, $request);
        }
    }

    private function handleRequest(
        ControllerEvent $event,
        AjaxRequestMiddlewareAttribute $ajaxRequestMiddlewareAttribute,
        Request $request
    ): void {
        try {
            call_user_func([$ajaxRequestMiddlewareAttribute->class, 'handle'], $request);
        } catch (AjaxRuntimeException) {
            $event->setController($this->getControllerJsonErrorCallable());
        }
    }

    private function getControllerJsonErrorCallable(): callable
    {
        return function (): JsonResponse {
            return new JsonResponse([
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR => $this->translator->trans('app.flash.form.create.error'),
                    FlashTypeConstant::WARNING => $this->translator->trans('app.flash.form.create.warning')
                ]
            ]);
        };
    }
}
