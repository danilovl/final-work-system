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

namespace App\DataTransferObject;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface DataTransferObjectInterface
{
    public function toArray(): array;

    public static function createFromArray(array $params, array|bool $requiredParamNames = true): static;

    public static function configureResolver(OptionsResolver $resolver, array $requiredOptionNames): void;
}
