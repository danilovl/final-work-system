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

namespace App\Model\Version\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\Media\Entity\Media;
use App\Model\Version\Security\Voter\Subject\VersionVoterSubject;
use App\Model\Work\Entity\Work;
use App\Model\Version\Http\Ajax\{
    VersionEditHandle,
    VersionCreateHandle,
    VersionDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class VersionController extends BaseController
{
    public function __construct(
        private VersionCreateHandle $versionCreateHandle,
        private VersionEditHandle $versionEditHandle,
        private VersionDeleteHandle $versionDeleteHandle
    ) {
    }

    public function create(Request $request, Work $work): JsonResponse
    {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);

        $this->denyAccessUnlessGranted(VoterSupportConstant::CREATE, $versionVoterSubject);

        return $this->versionCreateHandle->handle($request, $work);
    }

    /**
     * @ParamConverter("work", class="App\Model\Work\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Model\Media\Entity\Media", options={"id" = "id_media"})
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

        return $this->versionEditHandle->handle($request, $media);
    }

    /**
     * @ParamConverter("work", class="App\Model\Work\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Model\Media\Entity\Media", options={"id" = "id_media"})
     */
    public function delete(Work $work, Media $media): JsonResponse
    {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $versionVoterSubject);

        return $this->versionDeleteHandle->handle($media);
    }
}
