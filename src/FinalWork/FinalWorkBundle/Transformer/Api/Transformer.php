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

namespace FinalWork\FinalWorkBundle\Transformer\Api;

use DateTime;
use FinalWork\FinalWorkBundle\Services\ParametersService;
use ReflectionClass;
use ReflectionException;

class Transformer implements TransformerInterface
{
    /**
     * @var ParametersService
     */
    private $parametersService;

    /**
     * Transformer constructor.
     * @param ParametersService $parametersService
     */
    public function __construct(ParametersService $parametersService)
    {
        $this->parametersService = $parametersService;
    }

    /**
     * @param string $domain
     * @param array $fields
     * @param $object
     * @return array
     * @throws ReflectionException
     */
    public function transform(string $domain, array $fields, $object): array
    {
        $result = [];

        $apiFields = $this->parametersService
            ->getParam("api_fields.{$domain}");

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
                    $subFields = $subFields ?? $this->parametersService
                            ->getParam("api_fields.{$domain}.{$fieldValueClass}");

                    $result[$field] = $this->transform($domain, $subFields, $fieldValue);
                } else {
                    if ($fieldValue instanceof DateTime) {
                        $fieldValue = $fieldValue->format('Y-m-d');
                    }

                    $result[$field] = $fieldValue;
                }
            }
        }

        return $result;
    }
}