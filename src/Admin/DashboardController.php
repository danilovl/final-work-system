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

namespace App\Admin;

use App\Entity\{
    User,
    Group,
    EmailNotificationQueue
};
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    MenuItem,
    Dashboard
};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Final Work System');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('app.admin.menu.item.user', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('app.admin.menu.item.group', 'fas fa-users', Group::class);
        yield MenuItem::linkToCrud('app.admin.menu.item.email_notification_queue', 'fas fa-envelope-open', EmailNotificationQueue::class);
    }
}
