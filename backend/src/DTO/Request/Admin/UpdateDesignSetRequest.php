<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateDesignSetRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public ?string $description = null;
    public bool $hasDescription = false;

    public ?string $theme = null;
    public bool $hasTheme = false;

    public ?bool $isFree = null;

    public ?string $price = null;
    public bool $hasPrice = false;

    public ?string $type = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();

        if (isset($data['name']) && is_string($data['name'])) {
            $request->name = $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $request->hasDescription = true;
            $request->description = is_string($data['description']) ? $data['description'] : null;
        }

        if (array_key_exists('theme', $data)) {
            $request->hasTheme = true;
            $request->theme = is_string($data['theme']) ? $data['theme'] : null;
        }

        if (array_key_exists('isFree', $data) && is_bool($data['isFree'])) {
            $request->isFree = $data['isFree'];
        }

        if (array_key_exists('price', $data)) {
            $request->hasPrice = true;
            $request->price = is_string($data['price']) || is_numeric($data['price']) ? (string) $data['price'] : null;
        }

        if (isset($data['type']) && is_string($data['type'])) {
            $request->type = $data['type'];
        }

        return $request;
    }
}
