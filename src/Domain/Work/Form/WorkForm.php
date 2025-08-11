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

namespace App\Domain\Work\Form;

use App\Application\Constant\{
    FormConstant,
    DateFormatConstant
};
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use App\Domain\User\Form\DataGrid\UserDataGrid;
use App\Domain\Work\Model\WorkModel;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Form\DataGrid\WorkCategoryDataGrid;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkStatus\Form\DataGrid\WorkStatusDataGrid;
use App\Domain\WorkType\Entity\WorkType;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\{
    DateType,
    TextType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class WorkForm extends AbstractType
{
    final public const string NAME = 'work';

    public function __construct(
        private readonly WorkStatusDataGrid $workStatusDataGridHelper,
        private readonly WorkCategoryDataGrid $workCategoryDataGridHelper,
        private readonly UserDataGrid $userDataGridHelper
    ) {}

    /**
     * @param array{user: User} $options
     */
    #[Override]
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
                'query_builder' => fn (): QueryBuilder => $this->workStatusDataGridHelper->queryBuilder(),
            ])
            ->add('categories', EntityType::class, [
                'class' => WorkCategory::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => fn (): QueryBuilder => $this->workCategoryDataGridHelper->queryBuilderWorkCategoriesByOwner($user),
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
                'query_builder' => $this->callbackQueryBuilder(UserRoleConstant::STUDENT->value),
                'choice_label' => $this->callbackChoiceLabel()
            ])
            ->add('opponent', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'placeholder' => FormConstant::PLACEHOLDER->value,
                'query_builder' => $this->callbackQueryBuilder(UserRoleConstant::OPPONENT->value),
                'choice_label' => $this->callbackChoiceLabel()
            ])
            ->add('consultant', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'placeholder' => FormConstant::PLACEHOLDER->value,
                'query_builder' => $this->callbackQueryBuilder(UserRoleConstant::CONSULTANT->value),
                'choice_label' => $this->callbackChoiceLabel()
            ])
            ->add('deadline', DateType::class, [
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE->value,
                'html5' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('deadlineProgram', DateType::class, [
                'widget' => 'single_text',
                'format' => DateFormatConstant::WIDGET_SINGLE_TEXT_DATE->value,
                'required' => false,
                'html5' => false
            ]);
    }

    #[Override]
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

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    private function callbackQueryBuilder(string $role): callable
    {
        return fn (): QueryBuilder => $this->userDataGridHelper->queryBuilderAllByRole($role);
    }

    private function callbackChoiceLabel(): callable
    {
        return static fn (User $user): string => sprintf('%s (%s)', $user->getFullNameDegree(), $user->getUsername());
    }
}
