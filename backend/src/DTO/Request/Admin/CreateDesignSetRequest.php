<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDesignSetRequest
{
    #[Assert\NotNull]
    public ?int $designNameId = null;

    public string $type = 'full';

    public bool $isFree = true;

    public ?string $price = null;

    public ?string $theme = null;

    public ?string $description = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->designNameId = is_numeric($data['designNameId'] ?? null) ? (int) $data['designNameId'] : null;
        $request->type = is_string($data['type'] ?? null) ? $data['type'] : 'full';
        $request->isFree = is_bool($data['isFree'] ?? null) ? $data['isFree'] : true;
        $request->price = array_key_exists('price', $data) && (is_string($data['price']) || is_numeric($data['price'])) ? (string) $data['price'] : null;
        $request->theme = array_key_exists('theme', $data) && is_string($data['theme']) ? $data['theme'] : null;
        $request->description = array_key_exists('description', $data) && is_string($data['description']) ? $data['description'] : null;

        return $request;
    }
}
