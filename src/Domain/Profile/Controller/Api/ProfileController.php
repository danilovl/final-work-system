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

namespace App\Domain\Profile\Controller\Api;

use App\Domain\User\Entity\User;
use App\Domain\Profile\Http\ProfileImageHandle;
use Symfony\Component\HttpFoundation\{
    Response,
    BinaryFileResponse
};

readonly class ProfileController
{
    public function __construct(private ProfileImageHandle $profileImageHandle) {}

    public function image(User $user): BinaryFileResponse|Response
    {
        return $this->profileImageHandle->__invoke($user);
    }
}
