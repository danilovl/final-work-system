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

namespace App\Domain\Work\Controller\Api;

use App\Domain\Work\Http\Api\WorkListHandle;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class WorkController
{
    public function __construct(private WorkListHandle $workListHandle)
    {
    }

    public function list(Request $request, string $type): JsonResponse
    {
        return $this->workListHandle->handle($request, $type);
    }
}
