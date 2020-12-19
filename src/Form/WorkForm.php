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

use App\Constant\{
    FormConstant,
    DateFormatConstant,
    UserRoleConstant
};
use Doctrine\ORM\QueryBuilder;
use App\Model\Work\WorkModel;
use App\DataGrid\{
    UserDataGrid,
    WorkCategoryDataGrid,
    WorkStatusDataGrid
};
use App\Entity\User;
use App\Entity\{
    WorkType,
    WorkStatus,
    WorkCategory
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    DateType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class WorkForm extends AbstractType
{
    public const NAME = 'work';

    public function __construct(
        private WorkStatusDataGrid $workStatusDataGridHelper,
        private WorkCategoryDataGrid $workCategoryDataGridHelper,
        private UserDataGrid $userDataGridHelper
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('shortcut', TextType::class, [
                'required' => false
            ])
            ->add('status', EntityType::class, [
                'class' => WorkStatus::class,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ],
                'query_builder' => fn(): QueryBuilder => $this->workStatusDataGridHelper->queryBuilder(),
            ])
            ->add('categories', EntityType::class, [
                'class' => WorkCategory::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => fn(): QueryBuilder => $this->workCategoryDataGridHelper->queryBuilderWorkCategoriesByOwner($user),
            ])
            ->add('type', EntityType::class, [
                'class' => WorkType::class,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ],
                'query_builder' => $this->callbackQueryBuilder(UserRoleConstant::STUDENT),
                'choice_label' => $this->callbackChoiceLabel()
            ])
            ->add('opponent', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'placeholder' => FormConstant::PLACEHOLDER,
                'query_builder' => $this->callbackQueryBuilder(UserRoleConstant::OPPONENT),
                'choice_label' => $this->callbackChoiceLabel()
            ])
            ->add('consultant', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'placeholder' => FormConstant::PLACEHOLDER,
                'query_builder' => $this->callbackQueryBuilder(UserRoleConstant::CONSULTANT),
                'choice_label' => $this->callbackChoiceLabel()
            ])
            ->add('deadline', DateType::class, [
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE,
                'html5' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('deadlineProgram', DateType::class, [
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE,
                'required' => false,
                'html5' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => WorkModel::class
            ])
            ->setRequired([
                'user'
            ])
            ->setAllowedTypes('user', User::class);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    private function callbackQueryBuilder(string $role): callable
    {
        return fn(): QueryBuilder => $this->userDataGridHelper->queryBuilderAllByRole($role);
    }

    private function callbackChoiceLabel(): callable
    {
        return static fn(User $user): string => sprintf('%s (%s)', $user->getFullNameDegree(), $user->getUsername());
    }
}
