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

namespace App\Application\Transformer\Api;

use App\Application\Constant\DateFormatConstant;
use App\Application\Interfaces\Transformer\TransformerInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTimeImmutable;
use ReflectionClass;

readonly class Transformer implements TransformerInterface
{
    public function __construct(private ParameterServiceInterface $parameterService) {}

    public function transform(string $domain, array $fields, object $object): array
    {
        $result = [];

        $apiFields = $this->parameterService
            ->get("api_field.{$domain}");

        foreach ($fields as $item) {
            $field = $item;
            $subFields = null;
            if (is_array($item)) {
                $field = array_key_first($item);
                $subFields = $item[$field];
            }

            $getMethod = 'get' . ucfirst($field);

            if (method_exists($object, $getMethod)) {
                $fieldValue = $object->$getMethod();

                $fieldValueClass = null;
                if (is_object($fieldValue)) {
                    $fieldValueClass = (new ReflectionClass($fieldValue))->getShortName();
                }

                if ($fieldValueClass !== null && isset($apiFields[$fieldValueClass])) {
                    $subFields = $subFields ?? $this->parameterService->getArray("api_fields.{$domain}.{$fieldValueClass}");

                    $result[$field] = $this->transform($domain, $subFields, $fieldValue);
                } else {
                    if ($fieldValue instanceof DateTimeImmutable) {
                        $fieldValue = DateTimeImmutable::createFromInterface($fieldValue)->format(DateFormatConstant::DATE);
                    }

                    $result[$field] = $fieldValue;
                }
            }
        }

        return $result;
    }
}
