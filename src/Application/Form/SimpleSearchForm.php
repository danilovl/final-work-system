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

namespace App\Application\Form;

use App\Application\Form\Transformer\TrimTransformer;
use App\Application\Model\SearchModel;
use Override;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SimpleSearchForm extends AbstractType
{
    final public const string NAME = 'simple_search';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', TextType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank
            ]
        ]);

        $builder->get('search')->addViewTransformer(new TrimTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchModel::class,
            'method' => Request::METHOD_GET,
            'csrf_protection' => false
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
