<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\Profile\Controller\Ajax;

use App\Controller\BaseController;
use App\Model\Profile\Http\Ajax\ProfileCreateImageWebCameraHandle;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class ProfileController extends BaseController
{
    public function __construct(private ProfileCreateImageWebCameraHandle $profileCreateImageWebCameraHandle)
    {
    }

    public function createImageWebCamera(Request $request): JsonResponse
    {
        return $this->profileCreateImageWebCameraHandle->handle($request);
    }
}
