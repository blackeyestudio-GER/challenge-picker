<?php

namespace App\DTO\Request\Admin;

class PayoutDecisionRequest
{
    public ?string $adminNotes = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->adminNotes = is_string($data['adminNotes'] ?? null) ? $data['adminNotes'] : null;

        return $request;
    }
}
