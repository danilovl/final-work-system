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

namespace App\Domain\Work\Http\Api;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Bus\Command\CreateWork\CreateWorkCommand;
use App\Domain\Work\DTO\Api\Input\WorkInput;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Factory\WorkModelFactory;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse
};

readonly class WorkCreateHandle
{
    public function __construct(
        private UserService $userService,
        private WorkModelFactory $workModelFactory,
        private CommandBusInterface $commandBus,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(WorkInput $input): JsonResponse
    {
        $user = $this->userService->getUser();

        $workModel = $this->workModelFactory->fromWorkInput($input, $user);

        $command = CreateWorkCommand::create($workModel);
        /** @var Work $work */
        $work = $this->commandBus->dispatchResult($command);

        $workDto = $this->objectToDtoMapper->map($work, WorkDTO::class);

        return new JsonResponse(
            data: [
                'success' => true,
                'result' => $workDto
            ],
            status: Response::HTTP_CREATED
        );
    }
}
