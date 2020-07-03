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

use DateTime;
use App\Model\WorkSearch\WorkSearchModel;
use App\Entity\User;
use App\Entity\{
    WorkType,
    WorkStatus
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    ChoiceType
};
use Symfony\Component\Form\FormBuilderInterface;

class WorkSearchForm extends AbstractType
{
    public const NAME = 'work_search';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false
            ])
            ->add('shortcut', TextType::class, [
                'required' => false
            ])
            ->add('status', EntityType::class, [
                'class' => WorkStatus::class,
                'required' => false,
                'multiple' => true
            ])
            ->add('type', EntityType::class, [
                'class' => WorkType::class,
                'required' => false,
                'multiple' => true
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'multiple' => true,
                'choices' => $options['authors']
            ])
            ->add('supervisor', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'multiple' => true,
                'choices' => $options['supervisors']
            ])
            ->add('opponent', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'multiple' => true,
                'choices' => $options['opponents']
            ])
            ->add('consultant', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'multiple' => true,
                'choices' => $options['consultants']
            ])
            ->add('deadline', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $options['deadlines'],
                'choice_label' => static function (DateTime $deadline): string {
                    return (string)$deadline->format('Y-m-d');
                },
                'choice_value' => static function (DateTime $deadline): string {
                    return (string)$deadline->format('Y-m-d');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => WorkSearchModel::class,
                'authors' => [],
                'opponents' => [],
                'consultants' => [],
                'supervisors' => [],
                'deadlines' => []
            ])
            ->setAllowedTypes('authors', 'iterable')
            ->setAllowedTypes('opponents', 'iterable')
            ->setAllowedTypes('consultants', 'iterable')
            ->setAllowedTypes('supervisors', 'iterable')
            ->setAllowedTypes('deadlines', 'iterable');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
