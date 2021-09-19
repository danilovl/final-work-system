<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Form\Factory;

use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;

class FormDeleteFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService
    ) {
    }

    public function createDeleteForm($entity, string $route): FormInterface
    {
        return $this->formFactory->createBuilder()
            ->setAction($this->router->generate($route, [
                'id' => $this->hashidsService->encode($entity->getId())
            ]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm();
    }
}
