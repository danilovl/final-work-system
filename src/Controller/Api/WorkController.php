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

namespace App\Controller\Api;

use App\Constant\ApiDomainConstant;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    public function list(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit');
        $limit = $limit !== 0 ? $limit : null;

        $works = $this->get('app.facade.work')
            ->findAll($limit);

        $apiFields = $this->get('danilovl.parameter')
            ->get('api_fields.default.Work');

        $result = [];
        foreach ($works as $work) {
            $transformer = $this->get('app.transformer.api')
                ->transform(ApiDomainConstant::DEFAULT, $apiFields, $work);

            array_push($result, $transformer);
        }

        return new JsonResponse($result);
    }
}
