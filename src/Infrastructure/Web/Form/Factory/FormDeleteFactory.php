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

namespace App\Infrastructure\Web\Form\Factory;

use App\Application\Constant\FormOperationTypeConstant;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

readonly class FormDeleteFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService
    ) {}

    public function createDeleteForm(object $entity, string $route): FormInterface
    {
        if (!method_exists($entity, 'getId')) {
            throw new RuntimeException(sprintf('Object "%s" does not contains method getId.', $entity::class));
        }

        $action = $this->router->generate($route, [
            'id' => $this->hashidsService->encode($entity->getId())
        ]);

        return $this->formFactory
            ->createBuilder(options: [
                FormOperationTypeConstant::OPTION_KEY => FormOperationTypeConstant::DELETE
            ])
            ->setAction($action)
            ->setMethod(Request::METHOD_POST)
            ->getForm();
    }
}
