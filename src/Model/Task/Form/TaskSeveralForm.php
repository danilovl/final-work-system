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

namespace App\Model\Task\Form;

use App\Constant\WorkStatusConstant;
use App\Model\Task\Form\DataGrid\TaskDataGrid;
use App\Entity\{
    User,
    Work
};
use App\Model\Task\TaskModel;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskSeveralForm extends TaskForm
{
    public function __construct(private TaskDataGrid $taskDataGrid)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $supervisor = $options['supervisor'];

        $builder
            ->add('works', EntityType::class, [
                'class' => Work::class,
                'required' => true,
                'multiple' => true,
                'constraints' => [
                    new NotBlank
                ],
                'query_builder' => fn(): QueryBuilder => $this->taskDataGrid->queryBuilderWorksBySupervisor($supervisor, [WorkStatusConstant::ACTIVE]),
                'choice_label' => static fn(Work $work): string => sprintf('%s (%s)', $work->getTitle(), $work->getAuthor()->getFullNameDegree())
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => TaskModel::class
            ])
            ->setRequired([
                'supervisor'
            ])
            ->setAllowedTypes('supervisor', User::class);
    }
}
