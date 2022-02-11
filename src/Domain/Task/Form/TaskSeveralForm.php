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

namespace App\Domain\Task\Form;

use App\Application\Constant\WorkStatusConstant;
use App\Domain\Task\Form\DataGrid\TaskDataGrid;
use App\Domain\Task\TaskModel;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
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
                'query_builder' => $this->queryBuilder($supervisor),
                'choice_label' => $this->choiceLabel()
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

    private function queryBuilder(User $supervisor): callable
    {
        return fn(): QueryBuilder => $this->taskDataGrid->queryBuilderWorksBySupervisor($supervisor, [WorkStatusConstant::ACTIVE]);
    }

    private function choiceLabel(): callable
    {
        return static fn(Work $work): string => sprintf('%s (%s)', $work->getTitle(), $work->getAuthor()->getFullNameDegree());
    }
}
