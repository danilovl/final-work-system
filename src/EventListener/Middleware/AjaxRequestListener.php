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

namespace App\EventListener\Middleware;

use App\Attribute\AjaxRequestMiddlewareAttribute;
use App\Constant\FlashTypeConstant;
use App\Exception\AjaxRuntimeException;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxRequestListener
{
    public function __construct(
        private Reader $reader,
        private TranslatorInterface $translator
    ) {
    }

    public function onKernelController(ControllerEvent $event)
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
            $this->handleRequest($event, $attribute->newInstance(), $request);
        }
    }

    private function methodMiddleware(
        object $controller,
        string $method,
        ControllerEvent $event,
        Request $request
    ): void {
        $attributes = (new ReflectionClass($controller))->getMethod($method)->getAttributes(AjaxRequestMiddlewareAttribute::class);
        foreach ($attributes as $attribute) {
            $this->handleRequest($event, $attribute->newInstance(), $request);
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
            $event->setController(
                function () {
                    return new JsonResponse([
                        'valid' => false,
                        'notifyMessage' => [
                            FlashTypeConstant::ERROR => $this->translator->trans('app.flash.form.create.error'),
                            FlashTypeConstant::WARNING => $this->translator->trans('app.flash.form.create.warning')
                        ]
                    ]);
                }
            );
        }
    }
}