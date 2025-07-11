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

namespace App\Domain\Comment\Form;

use App\Domain\Comment\Form\EventSubscriber\EventCommentSubscriber;
use App\Domain\Comment\Model\CommentModel;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Override;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentForm extends AbstractType
{
    final public const string NAME = 'comment';

    /**
     * @param array{user: User, event: Event} $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextareaType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank
            ],
            'empty_data' => ''
        ]);

        $user = $options['user'];
        $event = $options['event'];

        $builder->addEventSubscriber(new EventCommentSubscriber($user, $event));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CommentModel::class
            ])
            ->setRequired([
                'user',
                'event'
            ])
            ->setAllowedTypes('user', User::class)
            ->setAllowedTypes('event', Event::class);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
