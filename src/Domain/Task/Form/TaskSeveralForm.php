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

use App\Domain\Task\Form\DataGrid\TaskDataGrid;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskSeveralForm extends TaskForm
{
    public function __construct(private readonly TaskDataGrid $taskDataGrid) {}

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        /** @var User $supervisor */
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

    #[Override]
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
        return fn(): QueryBuilder => $this->taskDataGrid->queryBuilderWorksBySupervisor($supervisor, [WorkStatusConstant::ACTIVE->value]);
    }

    private function choiceLabel(): callable
    {
        return static fn(Work $work): string => sprintf('%s (%s)', $work->getTitle(), $work->getAuthor()->getFullNameDegree());
    }
}
