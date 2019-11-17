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

namespace FinalWork\FinalWorkBundle\Utils;

use HTMLPurifier;
use HTMLPurifier_Config;
use Parsedown;

class Markdown
{
    /**
     * @var Parsedown
     */
    private $parser;

    /**
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * Markdown constructor.
     */
    public function __construct()
    {
        $this->parser = new Parsedown;

        $purifierConfig = HTMLPurifier_Config::create([
            'Cache.DefinitionImpl' => null, // Disable caching
        ]);
        $this->purifier = new HTMLPurifier($purifierConfig);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function toHtml($text): string
    {
        $html = $this->parser->text($text);
        $safeHtml = $this->purifier->purify($html);

        return $safeHtml;
    }
}
