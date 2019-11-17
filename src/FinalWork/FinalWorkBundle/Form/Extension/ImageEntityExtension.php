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

namespace FinalWork\FinalWorkBundle\Form\Extension;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    FormView,
    FormInterface,
    AbstractTypeExtension
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageEntityExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'show_image_entity',
            'show_image_entity_get'
        ]);

        $resolver->setDefault('show_image_entity', false);
        $resolver->setDefault('show_image_entity_get', null);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['show_image_entity'] = $options['show_image_entity'];
        $view->vars['show_image_entity_get'] = $options['show_image_entity_get'];
    }

    /**
     * @return iterable
     */
    public function getExtendedType(): string
    {
        return EntityType::class;
    }
}