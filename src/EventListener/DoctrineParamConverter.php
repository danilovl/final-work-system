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

namespace App\EventListener;

use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as BaseDoctrineParamConverter;

class DoctrineParamConverter extends BaseDoctrineParamConverter
{
    protected ?ManagerRegistry $registry;
    private array $defaultOptions;

    public function __construct(ManagerRegistry $registry = null)
    {
        parent::__construct($registry);

        $this->registry = $registry;
        $this->defaultOptions = [
            'entity_manager' => null,
            'exclude' => [],
            'mapping' => [],
            'strip_null' => false,
            'expr' => null,
            'id' => null,
            'repository_method' => null,
            'map_method_signature' => false,
            'evict_cache' => false,
        ];
    }

    protected function find(
        string $class,
        Request $request,
        array $options,
        string $name
    ): mixed {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        $id = $this->getIdentifierReplace($request, $options, $name);

        if (false === $id || null === $id) {
            return false;
        }

        $method = $options['repository_method'] ?? 'find';

        try {
            if (is_int($id)) {
                return $this->getManager($options['entity_manager'], $class)->getRepository($class)->$method($id);
            }

            throw new NoResultException;
        } catch (NoResultException) {
            return false;
        }
    }

    protected function getOptionsReplace(ParamConverter $configuration, bool $strict = true): array
    {
        $passedOptions = $configuration->getOptions();

        if (isset($passedOptions['repository_method'])) {
            @trigger_error('The repository_method option of @ParamConverter is deprecated and will be removed in 6.0. Use the expr option or @Entity.', E_USER_DEPRECATED);
        }

        if (isset($passedOptions['map_method_signature'])) {
            @trigger_error('The map_method_signature option of @ParamConverter is deprecated and will be removed in 6.0. Use the expr option or @Entity.', E_USER_DEPRECATED);
        }

        $extraKeys = array_diff(array_keys($passedOptions), array_keys($this->defaultOptions));
        if ($extraKeys && $strict) {
            throw new InvalidArgumentException(sprintf('Invalid option(s) passed to @%s: %s', $this->getAnnotationName($configuration), implode(', ', $extraKeys)));
        }

        return array_replace($this->defaultOptions, $passedOptions);
    }

    protected function getIdentifierReplace(Request $request, array $options, string $name): mixed
    {
        if (null !== $options['id']) {
            if (!is_array($options['id'])) {
                $name = $options['id'];
            } elseif (is_array($options['id'])) {
                $id = [];
                foreach ($options['id'] as $field) {
                    if (false !== strstr($field, '%s')) {
                        $field = sprintf($field, $name);
                    }
                    $id[$field] = $request->attributes->get($field);
                }

                return $id;
            }
        }

        if ($request->attributes->has($name)) {
            return $request->attributes->get($name);
        }

        if ($request->attributes->has('id') && !$options['id']) {
            return $request->attributes->get('id');
        }

        return false;
    }

    private function getManager(string $name, string $class): ObjectManager
    {
        if ($name === null) {
            return $this->registry->getManagerForClass($class);
        }

        return $this->registry->getManager($name);
    }

    private function getAnnotationName(ParamConverter $configuration): string
    {
        return (new ReflectionClass($configuration))->getShortName();
    }
}
