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
        private VersionCreateHandle $versionCreateHandle,
        private VersionEditHandle $versionEditHandle,
        private VersionDetailContentHandle $versionDetailContentHandle,
        private VersionDownloadHandle $versionDownloadHandle
    ) {
    }

    public function create(
        Request $request,
        Work $work
    ): Response {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);

        $this->denyAccessUnlessGranted(VoterSupportConstant::CREATE, $versionVoterSubject);

        return $this->versionCreateHandle->handle($request, $work);
    }

    #[ParamConverter('work', class: Work::class, options: ['id' => 'id_work'])]
    #[ParamConverter('media', class: Media::class, options: ['id' => 'id_media'])]
    public function edit(
        Request $request,
        Work $work,
        Media $media
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

    #[ParamConverter('work', class: Work::class, options: ['id' => 'id_work'])]
    #[ParamConverter('media', class: Media::class, options: ['id' => 'id_media'])]
    public function download(
        Work $work,
        Media $media
    ): BinaryFileResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $versionVoterSubject);

        return $this->versionDownloadHandle->handle($media);
    }

    #[ParamConverter('work', class: Work::class, options: ['id' => 'id_work'])]
    #[ParamConverter('media', class: Media::class, options: ['id' => 'id_media'])]
    public function downloadGoogle(
        Work $work,
        Media $media
    ): BinaryFileResponse {
        return $this->versionDownloadHandle->handle($media);
    }
}
