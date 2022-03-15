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

namespace App\Domain\Version\Controller\Api;

use App\Domain\Version\Http\Api\VersionListHandle;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VersionController extends AbstractController
{
    public function __construct(private VersionListHandle $versionListHandle)
    {
    }

    public function list(Request $request, Work $work): JsonResponse
    {
        return $this->versionListHandle->handle($request, $work);
    }
}
