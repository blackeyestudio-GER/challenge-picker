<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCategoryRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name = '';

    public ?string $description = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->name = is_string($data['name'] ?? null) ? $data['name'] : '';
        $request->description = array_key_exists('description', $data) && is_string($data['description']) ? $data['description'] : null;

        return $request;
    }
}
