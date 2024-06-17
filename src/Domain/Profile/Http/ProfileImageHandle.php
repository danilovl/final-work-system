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

namespace App\Domain\Profile\Http;

use App\Domain\Media\Service\MediaService;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class ProfileImageHandle
{
    public function __construct(private MediaService $mediaService) {}

    public function handle(User $user): BinaryFileResponse|Response
    {
        if (!$user->getProfileImage()) {
            return new Response;
        }

        return $this->mediaService->download($user->getProfileImage());
    }
}
