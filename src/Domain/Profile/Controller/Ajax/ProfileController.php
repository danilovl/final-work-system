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

namespace App\Domain\Profile\Controller\Ajax;

use App\Domain\Profile\Http\Ajax\ProfileCreateImageWebCameraHandle;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class ProfileController
{
    public function __construct(private ProfileCreateImageWebCameraHandle $profileCreateImageWebCameraHandle) {}

    public function createImageWebCamera(Request $request): JsonResponse
    {
        return $this->profileCreateImageWebCameraHandle->__invoke($request);
    }
}
