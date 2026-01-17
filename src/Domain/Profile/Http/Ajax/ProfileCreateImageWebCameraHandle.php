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

namespace App\Domain\Profile\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\RequestService;
use App\Domain\Profile\Bus\Command\ProfileCreateImageWebCamera\ProfileCreateImageWebCameraCommand;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class ProfileCreateImageWebCameraHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var array $imageData */
        $imageData = json_decode($request->getContent(), true);
        $imageData = $imageData['imageData'] ?? null;

        $user = $this->userService->getUser();

        $command = ProfileCreateImageWebCameraCommand::create($user, $imageData);
        $this->commandBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
    }
}
