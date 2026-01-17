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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\RequestService;
use App\Domain\UserGroup\Bus\Command\DeleteUserGroup\DeleteUserGroupCommand;
use App\Domain\UserGroup\Entity\Group;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class UserGroupDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Group $group): JsonResponse
    {
        $command = DeleteUserGroupCommand::create($group);
        $this->commandBus->dispatchResult($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
