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

namespace App\Infrastructure\Web\Twig\Runtime;

use App\Infrastructure\Web\Form\Factory\FormActionFactory;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use Symfony\Component\Form\FormView;

class FormActionRuntime
{
    public function __construct(private readonly FormActionFactory $formActionFactory) {}

    #[AsTwigFunction('action_form')]
    public function createActionForm(string $action): FormView
    {
        return $this->formActionFactory
            ->createActionForm($action)
            ->createView();
    }
}
