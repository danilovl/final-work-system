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

namespace App\Admin;

use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Crud,
    Action,
    Actions
};
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    TextField,
    FormField,
    ArrayField,
    IntegerField,
    BooleanField,
    DateTimeField,
    AssociationField
};

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setSearchFields(['id', 'username', 'email'])
            ->setDefaultSort(['lastLogin' => Criteria::DESC]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Account Information');
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('firstname');
        yield TextField::new('lastname');
        yield TextField::new('phone')->setRequired(false);
        yield TextField::new('username');
        yield TextField::new('email');
        yield DateTimeField::new('lastLogin');
        yield BooleanField::new('enabled');
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('updatedAt')->hideOnIndex();;

        yield FormField::addPanel('Security');
        yield ArrayField::new('roles');

        yield FormField::addPanel('Groups');
        yield AssociationField::new('groups')
            ->hideOnIndex()
            ->setCrudController(GroupCrudController::class)
            ->autocomplete();
    }

    public function configureActions(Actions $actions): Actions
    {
        $impersonate = Action::new('impersonate', 'app.admin.label.impersonate')
            ->linkToCrudAction('switchUserAction');

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $impersonate);
    }

    public function switchUserAction(AdminContext $context): RedirectResponse
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();

        return $this->redirectToRoute('profile_edit', ['_switch_user' => $user->getUsername()]);
    }
}
