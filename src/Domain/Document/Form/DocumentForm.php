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

namespace App\Domain\Document\Form;

use App\Domain\Media\Model\MediaModel;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\Form\DataGrid\MediaCategoryDataGrid;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\User\Entity\User;
use App\Infrastructure\Web\Form\Type\MediaFileType;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType,
    TextareaType,
    TextType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentForm extends AbstractType
{
    final public const string NAME = 'media';

    public function __construct(private readonly MediaCategoryDataGrid $categoryDataGridHelper) {}

    /**
     * @param array{user: User, mimeTypes: MediaMimeType[], uploadMedia: bool} $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank
                ],
                'empty_data' => ''
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('categories', EntityType::class, [
                'class' => MediaCategory::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => fn (): QueryBuilder => $this->categoryDataGridHelper->queryBuilderFindAllByOwner($user),
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('uploadMedia', MediaFileType::class, [
                'mimeTypes' => $options['mimeTypes'],
                'uploadMedia' => $options['uploadMedia']
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => MediaModel::class,
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

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
