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

namespace App\Infrastructure\Event\FormEventListener;

use App\Application\Constant\FormOperationTypeConstant;
use Override;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\{
    FormEvent,
    FormEvents,
    FormBuilderInterface
};
use App\Application\EventDispatcher\RequestFlashEventDispatcher;

class ValidationListenerExtension extends AbstractTypeExtension
{
    public function __construct(private readonly RequestFlashEventDispatcher $requestFlashEventDispatcher) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, $this->onPostSubmit(...));
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield from [FormType::class];
    }

    private function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        if ($form->getParent() !== null || !$form->isSubmitted()) {
            return;
        }

        if ($form->isValid()) {
            return;
        }

        /** @var FormOperationTypeConstant|null $operationType */
        $operationType = $form->getConfig()->getOption('operationType');
        if (!$operationType instanceof FormOperationTypeConstant) {
            return;
        }

        switch ($operationType) {
            case FormOperationTypeConstant::CREATE:
                $this->requestFlashEventDispatcher->onCreateFailure();

                break;
            case FormOperationTypeConstant::EDIT:
                $this->requestFlashEventDispatcher->onSaveFailure();

                break;
            case FormOperationTypeConstant::DELETE:
                $this->requestFlashEventDispatcher->onRemoveFailure();
        }
    }
}
