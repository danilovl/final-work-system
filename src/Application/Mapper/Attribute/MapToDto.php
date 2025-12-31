<?php declare(strict_types=1);

namespace App\Application\Mapper\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class MapToDto
{
    public function __construct(public string $dtoClass) {}
}
