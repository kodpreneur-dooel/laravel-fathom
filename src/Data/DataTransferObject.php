<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

abstract readonly class DataTransferObject
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        protected array $raw,
    ) {}

    /** @return array<string, mixed> */
    public function raw(): array
    {
        return $this->raw;
    }

    protected static function parseDate(?string $value): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        return CarbonImmutable::parse($value);
    }
}
