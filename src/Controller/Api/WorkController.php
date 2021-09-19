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

namespace App\Controller\Api;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    public function list(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_api.work.list')->handle($request);
    }
}
