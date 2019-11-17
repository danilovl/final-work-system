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

use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use LogicException;
use Roukmoute\HashidsBundle\Hashids;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HashidsParamConverterListener extends DoctrineParamConverter
{
    /**
     * @var Hashids
     */
    protected $hashids;

    /**
     * @var bool
     */
    private $autowire;

    /**
     * HashidsParamConverter constructor.
     * @param Hashids $hashids
     * @param ManagerRegistry $registry
     * @param $autowire
     */
    public function __construct(Hashids $hashids, ManagerRegistry $registry, $autowire)
    {
        parent::__construct($registry);

        $this->hashids = $hashids;
        $this->autowire = $autowire;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     * @throws  LogicException  When unable to guess how to get a id from the request information
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $exception = null;

        if ($this->autowire) {
            $name = $configuration->getName();
            $options = $this->getOptions($configuration);

            $hashid = $this->getIdentifier($request, $options, $name);
        } else {
            $options = $configuration->getOptions();

            if (!isset($options['id']) || mb_strtolower(mb_substr($options['id'], -6)) !== 'hashid') {
                return false;
            }

            $hashid = $request->attributes->get($options['id']);
        }

        return $this->decodeHashid($request, $configuration, $hashid, $exception);
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @param $hashid
     * @param Exception|null $exception
     *
     * @return bool
     * @throws Exception
     */
    private function decodeHashid(Request $request, ParamConverter $configuration, $hashid, Exception $exception = null): bool
    {
        $options = $configuration->getOptions();

        $id = $hashid;

        if (is_int($hashid) === false) {
            $decodeHashids = $this->hashids->decode($hashid);

            if (!is_array($decodeHashids) ||
                 !isset($decodeHashids[0]) ||
                 ($id = $decodeHashids[0]) === false ||
                 is_int($id) === false
            ) {
                if ($exception) {
                    throw $exception;
                }
                throw new LogicException('Unable to guess hashid from the request information.');
            }
        }

        $request->attributes->set('id', $id);
        unset($options['id']);

        $configuration->setOptions($options);
        $configuration->setIsOptional(true);

        parent::apply($request, $configuration);

        $name = $configuration->getName();

        if (!$request->attributes->get($name)) {
            throw new NotFoundHttpException(sprintf('%s "%s" not found.', ucfirst($name), $hashid));
        }

        return true;
    }
}
