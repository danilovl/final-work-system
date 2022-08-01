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

namespace App\Domain\UserGroup\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\UserGroup\Entity\Group;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserGroupDeleteHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService
    ) {}

    public function handle(Group $group): JsonResponse
    {
        $this->entityManagerService->remove($group);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
