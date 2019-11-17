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

namespace FinalWork\FinalWorkBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as BaseDoctrineParamConverter;

class DoctrineParamConverter extends BaseDoctrineParamConverter
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * DoctrineParamConverter constructor.
     * @param ManagerRegistry|null $registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        parent::__construct($registry);

        $this->registry = $registry;
    }

    /**
     * @param $class
     * @param Request $request
     * @param $options
     * @param $name
     * @return bool|mixed
     */
    protected function find($class, Request $request, $options, $name)
    {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        $id = $this->getIdentifier($request, $options, $name);

        if (false === $id || null === $id) {
            return false;
        }

        $method = $options['repository_method'] ?? 'find';

        try {
            if (is_int($id)) {
                return $this->getManager($options['entity_manager'], $class)->getRepository($class)->$method($id);
            }
            throw new NoResultException();
        } catch (NoResultException $e) {
            return false;
        }
    }

    /**
     * @param $name
     * @param $class
     * @return ObjectManager|null
     */
    private function getManager($name, $class): ObjectManager
    {
        if ($name === null) {
            return $this->registry->getManagerForClass($class);
        }

        return $this->registry->getManager($name);
    }
}
