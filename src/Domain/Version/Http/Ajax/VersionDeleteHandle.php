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

namespace App\Domain\Version\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Version\Bus\Command\DeleteVersion\DeleteVersionCommand;
use App\Infrastructure\Service\RequestService;
use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class VersionDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Media $media): JsonResponse
    {
        $command = DeleteVersionCommand::create($media);
        $this->commandBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
