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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ValidationListenerExtension extends AbstractTypeExtension
{
    public function __construct(private readonly RequestFlashEventDispatcher $requestFlashEventDispatcher) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->getDataClass() === null) {
            return;
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->onPostSubmit(...), -100);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(FormOperationTypeConstant::OPTION_KEY);
        $resolver->setDefault(FormOperationTypeConstant::OPTION_KEY, null);
        $resolver->setAllowedTypes(FormOperationTypeConstant::OPTION_KEY, [FormOperationTypeConstant::class, 'null']);
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield from [FormType::class];
    }

    private function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        if (!$form->isSubmitted() || $form->isValid()) {
            return;
        }

        /** @var FormOperationTypeConstant|null $operationType */
        $operationType = $form->getConfig()->getOption(FormOperationTypeConstant::OPTION_KEY);

        if (!$operationType instanceof FormOperationTypeConstant) {
            $object = $form->getData();

            if (!is_array($object) && !is_object($object)) {
                return;
            }

            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            if ($propertyAccessor->isReadable($object, 'id') === false) {
                return;
            }

            $id = $propertyAccessor->getValue($object, 'id');
            $operationType = $id === null ? FormOperationTypeConstant::CREATE : FormOperationTypeConstant::EDIT;
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
