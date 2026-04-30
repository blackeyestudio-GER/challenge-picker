<?php

namespace App\DTO\Request\Admin;

class UpdateCardDesignRequest
{
    public ?string $imageBase64 = null;
    public bool $hasImageBase64 = false;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();

        if (array_key_exists('imageBase64', $data)) {
            $request->hasImageBase64 = true;
            $request->imageBase64 = is_string($data['imageBase64']) ? $data['imageBase64'] : null;
        }

        return $request;
    }
}
