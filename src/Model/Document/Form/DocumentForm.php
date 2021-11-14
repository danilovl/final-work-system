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

namespace App\Model\Document\Form;

use App\Model\Media\MediaModel;
use App\Model\MediaCategory\Form\DataGrid\MediaCategoryDataGrid;
use Doctrine\ORM\QueryBuilder;
use App\Form\Type\MediaFileType;
use App\Entity\MediaCategory;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    CheckboxType,
    TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentForm extends AbstractType
{
    public const NAME = 'media';

    public function __construct(private MediaCategoryDataGrid $categoryDataGridHelper)
    {
    }

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
            ->add('categories', EntityType::class, [
                'class' => MediaCategory::class,
                'multiple' => true,
                'required' => false,
                'query_builder' => fn(): QueryBuilder => $this->categoryDataGridHelper->queryBuilderFindAllByOwner($user),
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

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
