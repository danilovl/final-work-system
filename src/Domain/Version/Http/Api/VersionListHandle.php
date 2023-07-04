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

namespace App\Domain\Version\Http\Api;

use App\Domain\Work\Service\WorkDetailTabService;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use App\Application\Constant\TabTypeConstant;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class VersionListHandle
{
    public function __construct(
        private WorkDetailTabService $workDetailTabService,
        private ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function handle(Request $request, Work $work): JsonResponse
    {
        $paginationVersion = $this->workDetailTabService->getTabPagination($request, TabTypeConstant::TAB_VERSION->value, $work);

        $versions = [];
        foreach ($paginationVersion as $version) {
            $versions[] = $this->objectToArrayTransformService->transform('api_key_field', $version);
        }

        return new JsonResponse([
            'count' => $paginationVersion->count(),
            'totalCount' => $paginationVersion->getTotalItemCount(),
            'success' => true,
            'result' => $versions
        ]);
    }
}
