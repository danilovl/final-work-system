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

namespace App\Model\Work\Http\Api;

use App\Constant\ApiDomainConstant;
use App\Model\Work\Facade\WorkFacade;
use App\Transformer\Api\Transformer;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkListHandle
{
    public function __construct(
        private WorkFacade $workFacade,
        private Transformer $transformer,
        private ParameterServiceInterface $parameterService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit');
        $limit = $limit !== 0 ? $limit : null;

        $works = $this->workFacade
            ->findAll($limit);

        $apiFields = $this->parameterService
            ->get('api_fields.default.Work');

        $result = [];
        foreach ($works as $work) {
            $transformer = $this->transformer
                ->transform(ApiDomainConstant::DEFAULT, $apiFields, $work);

            array_push($result, $transformer);
        }

        return new JsonResponse($result);
    }
}
