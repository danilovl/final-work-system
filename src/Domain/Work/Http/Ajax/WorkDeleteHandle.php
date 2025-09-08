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

namespace App\Domain\Work\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Work\Bus\Command\DeleteWork\DeleteWorkCommand;
use App\Application\Service\RequestService;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class WorkDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Work $work): JsonResponse
    {
        $editAuthorCommand = DeleteWorkCommand::create($work);
        $this->commandBus->dispatch($editAuthorCommand);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
