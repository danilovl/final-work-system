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

namespace App\Domain\WorkCategory\Http\Api;

use App\Domain\User\Service\UserService;
use App\Domain\WorkCategory\DTO\Api\WorkCategoryDTO;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class WorkCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private WorkCategoryFacade $workCategoryFacade,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        $categories = $this->workCategoryFacade->getAllByOwner($user);

        $result = [];
        foreach ($categories as $category) {
            $result[] = $this->objectToDtoMapper->map($category, WorkCategoryDTO::class);
        }

        return new JsonResponse($result);
    }
}
