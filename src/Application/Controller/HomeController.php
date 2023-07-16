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

namespace App\Application\Controller;

use App\Application\Cache\HomepageCache;
use App\Domain\SystemEvent\Facade\SystemEventFacade;
use App\Domain\User\Service\UserService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly HomepageCache $homepageCache,
        private readonly ParameterServiceInterface $parameterService,
        private readonly SystemEventFacade $systemEventFacade
    ) {}

    #[PermissionMiddleware(date: ['from' => '01-01-2023'])]
    public function index(Request $request): Response
    {
        $user = $this->userService->getUser();
        $page = $this->parameterService->getInt('pagination.default.page');

        $page = $request->query->getInt('page', $page);
        $pagePaginators = $this->homepageCache->createHomepagePaginator($user, $page);

        $isUnreadExist = $this->systemEventFacade
            ->isUnreadSystemEventsByRecipient($user);

        return $this->render('home/index.html.twig', [
            'isSystemEventUnreadExist' => $isUnreadExist,
            'paginator' => $pagePaginators[$page]
        ]);
    }
}
