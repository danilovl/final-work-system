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

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ConstantAwareTrait
{
    #[ORM\Column(name: 'constant', type: Types::STRING, nullable: false)]
    private ?string $constant = null;

    public function getConstant(): ?string
    {
        return $this->constant;
    }

    public function setConstant(string $constant): void
    {
        $this->constant = $constant;
    }
}
