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

use FinalWork\FinalWorkBundle\Entity\Conversation;
use FinalWork\FinalWorkBundle\Form\Constraint\ConversationMessageName;
use FinalWork\FinalWorkBundle\Model\ConversationMessage\ConversationComposeMessageModel;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    ChoiceType,
    TextareaType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\{
    InvalidOptionsException,
    MissingOptionsException,
    ConstraintDefinitionException
};

class ConversationComposeMessageForm extends AbstractType
{
    public const NAME = 'conversation_compose_message';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*** @var User $user */
        $user = $options['user'];

        $builder
            ->add('conversation', ChoiceType::class, [
                'required' => true,
                'multiple' => $user->isSupervisor(),
                'choices' => $options['conversations'],
                'choice_label' => function (Conversation $conversation): string {
                    return $this->choiceLabelConversation($conversation);
                },
                'constraints' => [
                    new NotBlank
                ]
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new  ConversationMessageName
                ],
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'data' => $user->getMessageHeaderFooter(),
                'constraints' => [
                    new NotBlank
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     * @throws AccessException
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * @param Conversation $conversation
     * @return string
     */
    private function choiceLabelConversation(Conversation $conversation): string
    {
        $title = '';
        $work = $conversation->getWork();
        if ($work !== null) {
            $type = null;
            $recipient = $conversation->getRecipient();

            $author = $work->getAuthor();
            if ($author !== null && $author->getId() === $recipient->getId()) {
                $type = $this->translator->trans('finalwork.form.label.author');
            }

            $opponent = $work->getOpponent();
            if ($opponent !== null && $opponent->getId() === $recipient->getId()) {
                $type = $this->translator->trans('finalwork.form.label.opponent');
            }

            $consultant = $work->getConsultant();
            if ($consultant !== null && $consultant->getId() === $recipient->getId()) {
                $type = $this->translator->trans('finalwork.form.label.consultant');
            }

            $supervisor = $work->getSupervisor();
            if ($supervisor !== null && $supervisor->getId() === $recipient->getId()) {
                $type = $this->translator->trans('finalwork.form.label.supervisor');
            }

            if ($type !== null) {
                $title = sprintf('%s(%s) | %s | %s',
                    $recipient->getFullNameDegree(),
                    mb_strtolower($type),
                    $work->getTitle(),
                    $work->getDeadline()->format('Y-m-d')
                );
            }
        }

        return $title;
    }
}
