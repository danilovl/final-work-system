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
use App\Application\Service\UserService;
use App\Domain\SystemEvent\Facade\SystemEventFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly HomepageCache $homepageCache,
        private readonly ParameterServiceInterface $parameterService,
        private readonly SystemEventFacade $systemEventFacade
    ) {
    }

    #[PermissionMiddleware(['date' => ['from' => '31-01-2020']])]
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
