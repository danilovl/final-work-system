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

namespace FinalWork\FinalWorkBundle\Controller\Api;

use FinalWork\FinalWorkBundle\Constant\ApiDomainConstant;
use FinalWork\FinalWorkBundle\Controller\BaseController;
use ReflectionException;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function listAction(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit');
        $limit = $limit !== 0 ? $limit : null;

        $works = $this->get('final_work.facade.work')
            ->findAll($limit);

        $apiFields = $this->get('final_work.parameters')
            ->getParam('api_fields.default.Work');

        $result = [];
        foreach ($works as $work) {
            $transformer = $this->get('final_work.transformer.api')
                ->transform(ApiDomainConstant::DEFAULT, $apiFields, $work);

            array_push($result, $transformer);
        }

        return new JsonResponse($result);
    }
}
