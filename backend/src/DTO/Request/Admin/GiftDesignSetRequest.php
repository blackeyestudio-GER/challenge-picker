<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class GiftDesignSetRequest
{
    #[Assert\NotBlank]
    public string $userIdentifier = '';

    #[Assert\NotNull]
    public ?int $designSetId = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->userIdentifier = is_string($data['userIdentifier'] ?? null) ? $data['userIdentifier'] : '';
        $request->designSetId = is_numeric($data['designSetId'] ?? null) ? (int) $data['designSetId'] : null;

        return $request;
    }
}
