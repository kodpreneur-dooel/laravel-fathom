<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class UserAccessData extends DataTransferObject
{
    /**
     * @param  array<int, string>  $teams
     */
    public function __construct(
        public string $level,
        public array $teams,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            level: (string) ($data['level'] ?? ''),
            teams: $data['teams'] ?? [],
            raw: $data,
        );
    }
}
