<?php

declare(strict_types=1);

namespace App\Entity;

final readonly class AuditLogViewData
{
    public function __construct(
        public int $id,
        public ?string $date,
        public ?string $userEmail,
        public ?string $action,
        public ?string $context,
        /**
         * @var array<string, array<mixed>>
         */
        public array $rawData,
        public string $data,
        public string $before,
        public string $after,
    ) {
    }
}