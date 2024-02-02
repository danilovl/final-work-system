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

namespace App\Domain\ConversationMessage\Form;

use App\Application\Constant\DateFormatConstant;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Form\Constraint\ConversationMessageName;
use App\Domain\ConversationMessage\Model\ConversationComposeMessageModel;
use App\Domain\User\Entity\User;
use App\Domain\User\Helper\UserRoleHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    ChoiceType,
    TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversationComposeMessageForm extends AbstractType
{
    final public const NAME = 'conversation_compose_message';

    public function __construct(private readonly TranslatorInterface $translator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*** @var User $user */
        $user = $options['user'];

        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new  ConversationMessageName
                ],
            ])
            ->add('conversation', ChoiceType::class, [
                'required' => true,
                'multiple' => UserRoleHelper::isSupervisor($user),
                'choices' => $options['conversations'],
                'choice_label' => fn(Conversation $conversation): string => $this->choiceLabelConversation($conversation),
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'data' => $user->getMessageHeaderFooter(),
                'constraints' => [
                    new NotBlank
                ]
            ]);

        if (!UserRoleHelper::isSupervisor($user)) {
            $builder->remove('name');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ConversationComposeMessageModel::class
            ])
            ->setRequired([
                'user',
                'conversations'
            ])
            ->setAllowedTypes('user', User::class)
            ->setAllowedTypes('conversations', 'iterable');
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    private function choiceLabelConversation(Conversation $conversation): string
    {
        $title = '';
        $work = $conversation->getWork();
        if ($work === null) {
            return $title;
        }

        $type = null;
        $recipient = $conversation->getRecipient();

        $author = $work->getAuthor();
        if ($author->getId() === $recipient->getId()) {
            $type = $this->translator->trans('app.form.label.author');
        }

        $opponent = $work->getOpponent();
        if ($opponent !== null && $opponent->getId() === $recipient->getId()) {
            $type = $this->translator->trans('app.form.label.opponent');
        }

        $consultant = $work->getConsultant();
        if ($consultant !== null && $consultant->getId() === $recipient->getId()) {
            $type = $this->translator->trans('app.form.label.consultant');
        }

        $supervisor = $work->getSupervisor();
        if ($supervisor->getId() === $recipient->getId()) {
            $type = $this->translator->trans('app.form.label.supervisor');
        }

        if ($type !== null) {
            $title = sprintf('%s(%s) | %s | %s',
                $recipient->getFullNameDegree(),
                mb_strtolower($type),
                $work->getTitle(),
                $work->getDeadline()->format(DateFormatConstant::DATE->value)
            );
        }

        return $title;
    }
}
