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

namespace App\Application\Helper;

use Symfony\Component\Form\{
    FormError,
    FormInterface
};

class FormValidationMessageHelper
{
    public static function getErrorMessages(FormInterface $form): array
    {
        $errors = array_map(static function (FormError $error): string {
            return $error->getMessage();
        }, iterator_to_array($form->getErrors()));

        foreach ($form->all() as $child) {
            if (!$child->isSubmitted() || !$child->isValid()) {
                $errors[$child->getName()] = self::getErrorMessages($child);
            }
        }

        return $errors;
    }
}
