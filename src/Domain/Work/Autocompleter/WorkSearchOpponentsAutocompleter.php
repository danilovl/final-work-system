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

namespace App\Domain\Work\Autocompleter;

use Danilovl\SelectAutocompleterBundle\Attribute\AsAutocompleter;

#[AsAutocompleter(alias: 'own.work-search-opponent')]
class WorkSearchOpponentsAutocompleter extends WorkSearchUsersAutocompleter {}
