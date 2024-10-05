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

namespace App\Domain\Work\Repository\Elastica;

use App\Domain\User\Entity\User;
use ArrayIterator;
use Symfony\Component\Form\FormInterface;

class ElasticaWorkRepository
{
    public function __construct(private readonly WorkSearch $workSearch) {}

    public function filterWorkList(
        User $user,
        string $type,
        FormInterface $form
    ): ArrayIterator {
        return $this->workSearch->filterWorkList($user, $type, $form);
    }
}
