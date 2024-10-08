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

namespace App\Domain\User\Admin;

use App\Domain\User\Entity\User;
use App\Domain\UserGroup\Admin\GroupCrudController;
use Doctrine\Common\Collections\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Action,
    Actions,
    Crud
};
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    ArrayField,
    AssociationField,
    BooleanField,
    DateTimeField,
    FormField,
    IntegerField,
    TextField
};
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserCrudController extends AbstractCrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setSearchFields(['id', 'username', 'email'])
            ->setDefaultSort(['lastLogin' => Order::Descending->value]);
    }

    #[Override]
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
        yield DateTimeField::new('updatedAt')->hideOnIndex();

        yield FormField::addPanel('Security');
        yield ArrayField::new('roles');

        yield FormField::addPanel('Groups');
        yield AssociationField::new('groups')
            ->hideOnIndex()
            ->setCrudController(GroupCrudController::class)
            ->autocomplete();
    }

    #[Override]
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
