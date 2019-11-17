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
use FinalWork\FinalWorkBundle\DataGrid\MediaCategoryDataGrid;
use FinalWork\FinalWorkBundle\Form\Type\MediaFileType;
use FinalWork\FinalWorkBundle\Entity\{
    Media,
    MediaCategory
};
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    CheckboxType,
    TextareaType
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

class DocumentForm extends AbstractType
{
    public const NAME = 'media';

    /**
     * @var MediaCategoryDataGrid
     */
    private $categoryDataGridHelper;

    /**
     * DocumentForm constructor.
     * @param MediaCategoryDataGrid $categoryDataGridHelper
     */
    public function __construct(MediaCategoryDataGrid $categoryDataGridHelper)
    {
        $this->categoryDataGridHelper = $categoryDataGridHelper;
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
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('categories', EntityType::class, array(
                'class' => MediaCategory::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => function () use ($user): QueryBuilder {
                    return $this->categoryDataGridHelper->queryBuilderFindAllByOwner($user);
                },
                'constraints' => [
                    new NotBlank
                ]
            ))
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('uploadMedia', MediaFileType::class, [
                'mimeTypes' => $options['mimeTypes'],
                'uploadMedia' => $options['uploadMedia']
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
                'data_class' => Media::class,
                'uploadMedia' => false
            ])
            ->setRequired([
                'user',
                'mimeTypes'
            ])
            ->setAllowedTypes('user', User::class)
            ->setAllowedTypes('uploadMedia', 'bool')
            ->setAllowedTypes('mimeTypes', 'iterable');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}