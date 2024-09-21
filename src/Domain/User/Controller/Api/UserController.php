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

namespace App\Domain\User\Controller\Api;

use App\Domain\User\Http\Api\UserDetailHandle;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class UserController
{
    public function __construct(private UserDetailHandle $userDetailHandle) {}

    public function detail(): JsonResponse
    {
        return $this->userDetailHandle->__invoke();
    }
}
