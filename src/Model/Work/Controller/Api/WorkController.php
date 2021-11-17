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

namespace App\Model\Work\Controller\Api;

use App\Controller\BaseController;
use App\Model\Work\Http\Api\WorkListHandle;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    public function __construct(private WorkListHandle $workListHandle)
    {
    }

    public function list(Request $request): JsonResponse
    {
        return $this->workListHandle->handle($request);
    }
}
