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

namespace FinalWork\SonataUserBundle\Admin;

use DateTime;
use Exception;
use RuntimeException;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Sonata\UserBundle\Form\Type\UserGenderListType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;

class UserAdmin extends BaseUserAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'lastLogin',
    ];

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('username')
            ->add('email')
            ->add('groups')
            ->add('enabled', null, ['editable' => true])
            ->add('lastLogin')
            ->add('createdAt');

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', 'string', ['template' => 'SonataUserBundle:Admin:Field/impersonating.html.twig']);
        }
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     * @throws Exception
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->tab('User')
            ->with('Profile', ['class' => 'col-md-6'])->end()
            ->with('General', ['class' => 'col-md-6'])->end()
            ->with('Social', ['class' => 'col-md-6'])->end()
            ->end()
            ->tab('Security')
            ->with('Status', ['class' => 'col-md-4'])->end()
            ->with('Groups', ['class' => 'col-md-4'])->end()
            ->with('Keys', ['class' => 'col-md-4'])->end()
            ->with('Roles', ['class' => 'col-md-12'])->end()
            ->end();

        $now = new DateTime();

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        if (method_exists(AbstractType::class, 'getBlockPrefix')) {
            $textType = TextType::class;
            $datePickerType = DatePickerType::class;
            $urlType = UrlType::class;
            $userGenderType = UserGenderListType::class;
            $localeType = LocaleType::class;
            $timezoneType = TimezoneType::class;
            $modelType = ModelType::class;
            $securityRolesType = SecurityRolesType::class;
        } else {
            $textType = 'text';
            $datePickerType = 'sonata_type_date_picker';
            $urlType = 'url';
            $userGenderType = 'sonata_user_gender';
            $localeType = 'locale';
            $timezoneType = 'timezone';
            $modelType = 'sonata_type_model';
            $securityRolesType = 'sonata_security_roles';
        }

        $formMapper
            ->tab('User')
            ->with('General')
            ->add('username')
            ->add('email')
            ->add('plainPassword', $textType, [
                'required' => !$this->getSubject() || $this->getSubject()->getId() === null,
            ])
            ->end()
            ->with('Profile')
            ->add('dateOfBirth', $datePickerType, [
                'years' => range(1900, $now->format('Y')),
                'dp_min_date' => '1-1-1900',
                'dp_max_date' => $now->format('c'),
                'required' => false,
            ])
            ->add('degreeBefore', null, [
                'required' => false,
                'label' => $this->trans('finalwork.form.label.degree_before', [], 'messages')
            ])
            ->add('firstname', null, ['required' => false])
            ->add('lastname', null, ['required' => false])
            ->add('degreeAfter', null, [
                'required' => false,
                'label' => $this->trans('finalwork.form.label.degree_after', [], 'messages')
            ])
            ->add('website', $urlType, ['required' => false])
            ->add('biography', $textType, ['required' => false])
            ->add('gender', $userGenderType, [
                'required' => true,
                'translation_domain' => $this->getTranslationDomain(),
            ])
            ->add('locale', $localeType, ['required' => false])
            ->add('timezone', $timezoneType, ['required' => false])
            ->add('phone', null, ['required' => false])
            ->end()
            ->with('Social')
            ->add('skype', null, ['required' => false])
            ->add('facebookUid', null, ['required' => false])
            ->add('facebookName', null, ['required' => false])
            ->add('twitterUid', null, ['required' => false])
            ->add('twitterName', null, ['required' => false])
            ->add('gplusUid', null, ['required' => false])
            ->add('gplusName', null, ['required' => false])
            ->end()
            ->end()
            ->tab('Security')
            ->with('Status')
            ->add('enabled', null, ['required' => false])
            ->end()
            ->with('Groups')
            ->add('groups', $modelType, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->end()
            ->with('Roles')
            ->add('realRoles', $securityRolesType, [
                'label' => 'form.label_roles',
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            ->end()
            ->with('Keys')
            ->add('token', null, ['required' => false])
            ->add('twoStepVerificationCode', null, ['required' => false])
            ->end()
            ->end();
    }
}
