<?php

namespace App\Services;

class TenantContext
{
    private ?int $congregationId = null;

    public function set(int $id): void
    {
        $this->congregationId = $id;
    }

    public function get(): ?int
    {
        return $this->congregationId;
    }

    public function isSet(): bool
    {
        return $this->congregationId !== null;
    }

    public function clear(): void
    {
        $this->congregationId = null;
    }
}
