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

use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;

readonly class FormActionFactory
{
    final public const string NAME = 'action_form';

    public function __construct(private FormFactoryInterface $formFactory) {}

    public function createActionForm(?string $action = null): FormInterface
    {
        $builder = $this->formFactory
            ->createNamedBuilder(self::NAME)
            ->setMethod(Request::METHOD_POST);

        if ($action !== null) {
            $builder->setAction($action);
        }

        return $builder->getForm();
    }

    public function isSubmittedAndValid(Request $request): bool
    {
        $form = $this->createActionForm();
        $form->handleRequest($request);

        return $form->isSubmitted() && $form->isValid();
    }
}
