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

namespace App\Domain\UserGroup\Admin;

use App\Domain\UserGroup\Entity\Group;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    DateTimeField,
    FormField,
    IntegerField,
    TextField
};
use Override;

class GroupCrudController extends AbstractCrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Group::class;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Group')
            ->setEntityLabelInPlural('Groups');
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Information');
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('name');
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('updatedAt')->hideOnIndex();
    }
}
