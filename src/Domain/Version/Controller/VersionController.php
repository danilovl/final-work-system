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

namespace App\Domain\Version\Controller;

use App\Application\Constant\VoterSupportConstant;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Domain\Version\Http\{
    VersionEditHandle,
    VersionCreateHandle,
    VersionDownloadHandle,
    VersionDetailContentHandle
};
use App\Domain\Media\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Domain\Version\Security\Voter\Subject\VersionVoterSubject;
use App\Domain\Work\Entity\Work;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    Request,
    Response
};

class VersionController extends AbstractController
{
    public function __construct(
        private readonly VersionCreateHandle $versionCreateHandle,
        private readonly VersionEditHandle $versionEditHandle,
        private readonly VersionDetailContentHandle $versionDetailContentHandle,
        private readonly VersionDownloadHandle $versionDownloadHandle
    ) {}

    public function create(
        Request $request,
        Work $work
    ): Response {
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

        return $this->versionEditHandle->handle($request, $work, $media);
    }

    public function detailContent(Media $version): Response
    {
        $versionVoterSubject = (new VersionVoterSubject)->setMedia($version);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $versionVoterSubject, 'The work media does not exist');

        return $this->versionDetailContentHandle->handle($version);
    }

    public function download(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_media' => 'id'])] Media $media
    ): BinaryFileResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $versionVoterSubject);

        return $this->versionDownloadHandle->handle($media);
    }

    public function downloadGoogle(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_media' => 'id'])] Media $media
    ): BinaryFileResponse {
        return $this->versionDownloadHandle->handle($media);
    }
}
