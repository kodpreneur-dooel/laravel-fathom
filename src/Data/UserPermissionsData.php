<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class UserPermissionsData extends DataTransferObject
{
    public function __construct(
        public UserAccessData $settingsAccess,
        public UserAccessData $viewAccess,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            settingsAccess: UserAccessData::fromArray($data['settings_access'] ?? []),
            viewAccess: UserAccessData::fromArray($data['view_access'] ?? []),
            raw: $data,
        );
    }
}
