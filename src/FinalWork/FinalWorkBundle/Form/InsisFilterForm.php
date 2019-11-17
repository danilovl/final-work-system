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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsisFilterForm extends AbstractType
{
    /**
     * @var array
     */
    private $type;

    /**
     * @var array
     */
    private $year;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->year = $options['year'];
        $this->type = $options['type'];

        $builder
            ->add('year', ChoiceType::class, [
                'choices' => $this->year
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $this->type
            ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'year' => null,
            'type' => null
        ]);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'insis_filter';
    }
}
