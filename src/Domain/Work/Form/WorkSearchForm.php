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

namespace App\Domain\Work\Form;

use App\Application\Constant\DateFormatConstant;
use App\Domain\User\Entity\User;
use App\Domain\WorkSearch\Model\WorkSearchModel;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkSearchForm extends AbstractType
{
    final public const string NAME = 'work_search';

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
            ]);

        $types = ['author', 'supervisor', 'opponent', 'consultant'];
        foreach ($types as $type) {
            $builder->add($type, ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => $options[$type . 's'],
                'choice_label' => static fn(User $user): string => $user->getFullNameDegree()
            ]);
        }

        $builder->add('deadline', ChoiceType::class, [
            'required' => false,
            'multiple' => true,
            'choices' => $options['deadlines'],
            'choice_label' => static fn(DateTime $deadline): string => $deadline->format(DateFormatConstant::DATE->value),
            'choice_value' => static fn(DateTime $deadline): string => $deadline->format(DateFormatConstant::DATE->value)
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
