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

use App\Annotation\AjaxRequestMiddleware;
use App\Constant\FlashTypeConstant;
use App\Exception\AjaxRuntimeException;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxRequestListener
{
    private Reader $reader;
    private TranslatorInterface $translator;

    public function __construct(
        Reader $reader,
        TranslatorInterface $translator
    ) {
        $this->reader = $reader;
        $this->translator = $translator;
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

        [$controller, $methodName] = $controllers;

        $reflectionClass = new ReflectionClass($controller);
        /** @var AjaxRequestMiddleware $classAnnotation */
        $classAnnotation = $this->reader
            ->getClassAnnotation($reflectionClass, AjaxRequestMiddleware::class);

        if ($classAnnotation !== null) {
            $this->handleRequest($event, $classAnnotation, $request);
        }

        $reflectionObject = new ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        /** @var AjaxRequestMiddleware $methodAnnotation */
        $methodAnnotation = $this->reader
            ->getMethodAnnotation($reflectionMethod, AjaxRequestMiddleware::class);

        if ($methodAnnotation !== null) {
            $this->handleRequest($event, $methodAnnotation, $request);
        }
    }

    private function handleRequest(
        ControllerEvent $event,
        AjaxRequestMiddleware $ajaxRequestMiddleware,
        Request $request
    ): void {
        try {
            call_user_func([$ajaxRequestMiddleware->class, 'handle'], $request);
        } catch (AjaxRuntimeException $exception) {
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