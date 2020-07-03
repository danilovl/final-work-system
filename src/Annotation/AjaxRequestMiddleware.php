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

namespace App\Annotation;

/**
 * @Annotation
 */
class AjaxRequestMiddleware
{
    public string $class;

    public function __construct(array $options)
    {
        $this->class = $options['class'];
    }
}