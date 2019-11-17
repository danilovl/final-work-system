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

namespace FinalWork\FinalWorkBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ConstantAwareTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="constant", type="string", nullable=false))
     */
    private $constant;

    /**
     * @return string
     */
    public function getConstant(): ?string
    {
        return $this->constant;
    }

    /**
     * @param string $constant
     */
    public function setConstant(string $constant): void
    {
        $this->constant = $constant;
    }
}
