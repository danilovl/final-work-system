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

namespace App\Controller;

use App\Constant\VoterSupportConstant;
use App\Model\Version\Http\{
    VersionEditHandle,
    VersionCreateHandle,
    VersionDownloadHandle,
    VersionDetailContentHandle
};
use App\Security\Voter\Subject\VersionVoterSubject;
use App\Entity\{
    Work,
    Media
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    BinaryFileResponse
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class VersionController extends BaseController
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

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\Media", options={"id" = "id_media"})
     */
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

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\media", options={"id" = "id_media"})
     */
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

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\media", options={"id" = "id_media"})
     */
    public function downloadGoogle(
        Work $work,
        Media $media
    ): BinaryFileResponse {
        return $this->versionDownloadHandle->handle($media);
    }
}
