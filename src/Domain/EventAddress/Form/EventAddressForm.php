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

namespace App\Domain\EventAddress\Form;

use App\Application\Form\Transformer\CommaDotTransformer;
use App\Domain\EventAddress\Model\EventAddressModel;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType,
    TextareaType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventAddressForm extends AbstractType
{
    final public const string NAME = 'event_address';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('street', TextType::class, [
                'required' => false
            ])
            ->add('latitude', TextType::class, [
                'required' => false
            ])
            ->add('longitude', TextType::class, [
                'required' => false
            ])
            ->add('skype', CheckboxType::class, [
                'required' => false
            ]);

        $builder->get('latitude')->addViewTransformer(new CommaDotTransformer);
        $builder->get('longitude')->addViewTransformer(new CommaDotTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventAddressModel::class
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
