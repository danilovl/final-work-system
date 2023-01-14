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

namespace App\Domain\Version\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Media\Entity\Media;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Domain\Version\Http\Ajax\{
    VersionEditHandle,
    VersionCreateHandle,
    VersionDeleteHandle
};
use App\Domain\Version\Security\Voter\Subject\VersionVoterSubject;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VersionController extends AbstractController
{
    public function __construct(
        private readonly VersionCreateHandle $versionCreateHandle,
        private readonly VersionEditHandle $versionEditHandle,
        private readonly VersionDeleteHandle $versionDeleteHandle
    ) {}

    public function create(Request $request, Work $work): JsonResponse
    {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);

        $this->denyAccessUnlessGranted(VoterSupportConstant::CREATE, $versionVoterSubject);

        return $this->versionCreateHandle->handle($request, $work);
    }

    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_media' => 'id'])] Media $media
    ): Response {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $versionVoterSubject);

        return $this->versionEditHandle->handle($request, $media);
    }

    public function delete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_media' => 'id'])] Media $media
    ): JsonResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $versionVoterSubject);

        return $this->versionDeleteHandle->handle($media);
    }
}
