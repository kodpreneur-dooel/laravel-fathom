<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

/**
 * @template T
 */
readonly class PaginatedResponse extends DataTransferObject
{
    /**
     * @param  array<int, T>  $items
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?int $limit,
        public ?string $nextCursor,
        public array $items,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    public function hasMore(): bool
    {
        return $this->nextCursor !== null;
    }

    public function nextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  callable(array<string, mixed>): T  $mapper
     * @return self<T>
     */
    public static function fromArray(array $data, callable $mapper): self
    {
        $items = array_map(
            fn (array $item): mixed => $mapper($item),
            $data['items'] ?? []
        );

        return new self(
            limit: isset($data['limit']) ? (int) $data['limit'] : null,
            nextCursor: $data['next_cursor'] ?? null,
            items: $items,
            raw: $data,
        );
    }
}
