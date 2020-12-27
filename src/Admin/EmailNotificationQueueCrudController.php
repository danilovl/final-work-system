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

use App\Entity\EmailNotificationQueue;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class EmailNotificationQueueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EmailNotificationQueue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Email Notification Queue')
            ->setEntityLabelInPlural('Email Notification Queue')
            ->setDefaultSort(['sendedAt' => Criteria::DESC]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Information');
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('subject');
        yield TextField::new('to');
        yield TextField::new('from');
        yield TextEditorField::new('body')->hideOnIndex();
        yield DateTimeField::new('sendedAt');
        yield BooleanField::new('success');
    }
}