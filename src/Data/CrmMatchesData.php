<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class CrmMatchesData extends DataTransferObject
{
    /**
     * @param  array<int, CrmContactMatchData>  $contacts
     * @param  array<int, CrmCompanyMatchData>  $companies
     * @param  array<int, CrmDealMatchData>  $deals
     */
    public function __construct(
        public array $contacts,
        public array $companies,
        public array $deals,
        public ?string $error,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            contacts: array_map(
                fn (array $item): CrmContactMatchData => CrmContactMatchData::fromArray($item),
                $data['contacts'] ?? []
            ),
            companies: array_map(
                fn (array $item): CrmCompanyMatchData => CrmCompanyMatchData::fromArray($item),
                $data['companies'] ?? []
            ),
            deals: array_map(
                fn (array $item): CrmDealMatchData => CrmDealMatchData::fromArray($item),
                $data['deals'] ?? []
            ),
            error: $data['error'] ?? null,
            raw: $data,
        );
    }
}
