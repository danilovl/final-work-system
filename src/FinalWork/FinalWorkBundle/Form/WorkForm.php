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

namespace FinalWork\FinalWorkBundle\Form;

use Doctrine\ORM\QueryBuilder;
use FinalWork\FinalWorkBundle\Constant\FormConstant;
use FinalWork\FinalWorkBundle\Model\Work\WorkModel;
use FinalWork\FinalWorkBundle\DataGrid\{
    UserDataGrid,
    WorkCategoryDataGrid,
    WorkStatusDataGrid
};
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\{
    WorkType,
    WorkStatus,
    WorkCategory
};
use FinalWork\FinalWorkBundle\Constant\UserRoleConstant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    DateType,
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\{
    InvalidOptionsException,
    MissingOptionsException,
    ConstraintDefinitionException
};

class WorkForm extends AbstractType
{
    public const NAME = 'work';

    /**
     * @var WorkStatusDataGrid
     */
    private $workStatusDataGridHelper;

    /**
     * @var WorkCategoryDataGrid
     */
    private $workCategoryDataGridHelper;

    /**
     * @var UserDataGrid
     */
    private $userDataGridHelper;

    /**
     * WorkForm constructor.
     * @param WorkStatusDataGrid $workStatusDataGridHelper
     * @param WorkCategoryDataGrid $workCategoryDataGridHelper
     * @param UserDataGrid $userDataGridHelper
     */
    public function __construct(
        WorkStatusDataGrid $workStatusDataGridHelper,
        WorkCategoryDataGrid $workCategoryDataGridHelper,
        UserDataGrid $userDataGridHelper
    ) {
        $this->workStatusDataGridHelper = $workStatusDataGridHelper;
        $this->workCategoryDataGridHelper = $workCategoryDataGridHelper;
        $this->userDataGridHelper = $userDataGridHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     */
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
                'query_builder' => function (): QueryBuilder {
                    return $this->workStatusDataGridHelper->queryBuilder();
                },
            ])
            ->add('categories', EntityType::class, [
                'class' => WorkCategory::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => function () use ($user): QueryBuilder {
                    return $this->workCategoryDataGridHelper->queryBuilderWorkCategoriesByOwner($user);
                },
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
                'query_builder' => function (): QueryBuilder {
                    return $this->userDataGridHelper->queryBuilderAllByRole(UserRoleConstant::STUDENT);
                },
                'choice_label' => static function (User $user): string {
                    return sprintf('%s (%s)', $user->getFullNameDegree(), $user->getUsername());
                },
                'show_image_entity' => true
            ])
            ->add('opponent', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'placeholder' => FormConstant::PLACEHOLDER,
                'query_builder' => function (): QueryBuilder {
                    return $this->userDataGridHelper->queryBuilderAllByRole(UserRoleConstant::OPPONENT);
                },
                'choice_label' => static function (User $user): string {
                    return sprintf('%s (%s)', $user->getFullNameDegree(), $user->getUsername());
                },
                'show_image_entity' => true,
                'show_image_entity_get' => 'getProfileImagePath',
            ])
            ->add('consultant', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'placeholder' => FormConstant::PLACEHOLDER,
                'query_builder' => function (): QueryBuilder {
                    return $this->userDataGridHelper->queryBuilderAllByRole(UserRoleConstant::CONSULTANT);
                },
                'choice_label' => static function (User $user): string {
                    return sprintf('%s (%s)', $user->getFullNameDegree(), $user->getUsername());
                }
            ])
            ->add('deadline', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('deadlineProgram', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'html5' => false
            ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
