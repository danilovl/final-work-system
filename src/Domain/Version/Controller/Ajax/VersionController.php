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
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Media\Entity\Media;
use App\Domain\Version\Http\Ajax\{
    VersionCreateHandle,
    VersionDeleteHandle,
    VersionEditHandle
};
use App\Domain\Version\Security\Voter\Subject\VersionVoterSubject;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};

readonly class VersionController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private VersionCreateHandle $versionCreateHandle,
        private VersionEditHandle $versionEditHandle,
        private VersionDeleteHandle $versionDeleteHandle
    ) {}

    public function create(Request $request, Work $work): JsonResponse
    {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);

        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::CREATE->value, $versionVoterSubject);

        return $this->versionCreateHandle->__invoke($request, $work);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_media'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_media' => 'id'])] Media $media
    ): Response {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $versionVoterSubject);

        return $this->versionEditHandle->__invoke($request, $media);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_media'])]
    public function delete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_media' => 'id'])] Media $media
    ): JsonResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $versionVoterSubject);

        return $this->versionDeleteHandle->__invoke($media);
    }
}
