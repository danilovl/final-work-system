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

use App\Domain\Work\Entity\Work;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class WorkDetailHandle
{
    public function __construct(private ObjectToArrayTransformService $objectToArrayTransformService) {}

    public function __invoke(Work $work): JsonResponse
    {
        $result = $this->objectToArrayTransformService->transform('api_key_field', $work);

        return new JsonResponse($result);
    }
}
