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

namespace App\Form;

use App\Form\Transformer\CommaDotTransformer;
use App\Model\EventAddress\EventAddressModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    CheckboxType,
    TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventAddressForm extends AbstractType
{
    public const NAME = 'event_address';

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

        $builder->get('latitude')->addViewTransformer(new CommaDotTransformer());
        $builder->get('longitude')->addViewTransformer(new CommaDotTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventAddressModel::class
        ]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
