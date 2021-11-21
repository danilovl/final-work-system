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

namespace App\Model\Work\Form;

use App\Model\WorkStatus\Entity\WorkStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Symfony\Component\Validator\Constraints\NotBlank;

class WorkSearchStatusForm extends AbstractType
{
    public const NAME = 'work_search';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', EntityType::class, [
            'class' => WorkStatus::class,
            'required' => true,
            'multiple' => true,
            'constraints' => [
                new NotBlank
            ]
        ]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
