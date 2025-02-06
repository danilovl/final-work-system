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

namespace App\Domain\Task\Form;

use App\Application\Constant\DateFormatConstant;
use App\Domain\Task\Model\TaskModel;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType,
    DateType,
    TextareaType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskForm extends AbstractType
{
    final public const string NAME = 'task';

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
            ->add('complete', CheckboxType::class, [
                'required' => false
            ])
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('deadline', DateType::class, [
                'required' => true,
                'html5' => false,
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE->value,
                'constraints' => [
                    new NotBlank
                ]
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskModel::class
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
