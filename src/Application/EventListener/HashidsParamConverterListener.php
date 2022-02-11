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

namespace App\Application\EventListener;

use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HashidsParamConverterListener extends DoctrineParamConverter
{
    private bool $autowire;

    public function __construct(
        private HashidsServiceInterface $hashids,
        ManagerRegistry $registry
    ) {
        parent::__construct($registry);

        $this->autowire = true;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $exception = null;

        if ($this->autowire) {
            $name = $configuration->getName();
            $options = $this->getOptionsReplace($configuration);

            $hashid = $this->getIdentifierReplace($request, $options, $name);
        } else {
            $options = $configuration->getOptions();

            if (!isset($options['id']) || mb_strtolower(mb_substr($options['id'], -6)) !== 'hashid') {
                return false;
            }

            $hashid = $request->attributes->get($options['id']);
        }

        return $this->decodeHashid($request, $configuration, $hashid, $exception);
    }

    private function decodeHashid(
        Request $request,
        ParamConverter $configuration,
        string $hashid,
        Exception $exception = null
    ): bool {
        $options = $configuration->getOptions();

        $id = $hashid;

        if (!is_int($hashid)) {
            $decodeHashids = $this->hashids->decode($hashid);
            $id = $decodeHashids[0];

            if (!is_array($decodeHashids) ||
                !isset($decodeHashids[0]) ||
                $id === false ||
                !is_int($id)
            ) {
                if ($exception) {
                    throw new $exception;
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
