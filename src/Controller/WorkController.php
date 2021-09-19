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
use App\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.work.create')->handle($request);
    }

    public function detail(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $work);

        return $this->get('app.http_handle.work.detail')->handle($request, $work);
    }

    public function list(Request $request, string $type): Response
    {
        return $this->get('app.http_handle.work.list')->handle($request, $type);
    }

    public function edit(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->get('app.http_handle.work.edit')->handle($request, $work);
    }

    public function editAuthor(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->get('app.http_handle.work.edit_author')->handle($request, $work);
    }

    public function delete(Request $request, Work $work): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        return $this->get('app.http_handle.work.delete')->handle($request, $work);
    }
}
