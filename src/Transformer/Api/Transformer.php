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

namespace App\Transformer\Api;

use App\Constant\DateFormatConstant;
use Danilovl\ParameterBundle\Services\ParameterService;
use DateTime;
use ReflectionClass;

class Transformer implements TransformerInterface
{
    public function __construct(private ParameterService $parameterService)
    {
    }

    public function transform(string $domain, array $fields, mixed $object): array
    {
        $result = [];

        $apiFields = $this->parameterService
            ->get("api_fields.{$domain}");

        foreach ($fields as $item) {
            $field = $item;
            $subFields = null;
            if (is_array($item)) {
                $field = array_key_first($item);
                $subFields = $item[$field];
            }

            $getMethod = 'get' . ucfirst($field);

            if (method_exists($object, $getMethod)) {
                $fieldValue = call_user_func_array([$object, $getMethod], []);

                $fieldValueClass = null;
                if (is_object($fieldValue)) {
                    $fieldValueClass = (new ReflectionClass($fieldValue))->getShortName();
                }

                if ($fieldValueClass !== null && isset($apiFields[$fieldValueClass])) {
                    $subFields = $subFields ?? $this->parameterService->get("api_fields.{$domain}.{$fieldValueClass}");

                    $result[$field] = $this->transform($domain, $subFields, $fieldValue);
                } else {
                    if ($fieldValue instanceof DateTime) {
                        $fieldValue = $fieldValue->format(DateFormatConstant::DATE);
                    }

                    $result[$field] = $fieldValue;
                }
            }
        }

        return $result;
    }
}
