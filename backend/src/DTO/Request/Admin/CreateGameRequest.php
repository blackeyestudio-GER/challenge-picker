<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class CreateGameRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name = '';

    public ?string $description = null;

    public ?string $image = null;

    public bool $isCategoryRepresentative = false;

    public ?string $steamLink = null;

    public ?string $epicLink = null;

    public ?string $gogLink = null;

    public ?string $twitchCategory = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->name = is_string($data['name'] ?? null) ? $data['name'] : '';
        $request->description = array_key_exists('description', $data) && is_string($data['description']) ? $data['description'] : null;
        $request->image = array_key_exists('image', $data) && is_string($data['image']) ? $data['image'] : null;
        $request->isCategoryRepresentative = is_bool($data['isCategoryRepresentative'] ?? null) ? $data['isCategoryRepresentative'] : false;
        $request->steamLink = array_key_exists('steamLink', $data) && is_string($data['steamLink']) ? $data['steamLink'] : null;
        $request->epicLink = array_key_exists('epicLink', $data) && is_string($data['epicLink']) ? $data['epicLink'] : null;
        $request->gogLink = array_key_exists('gogLink', $data) && is_string($data['gogLink']) ? $data['gogLink'] : null;
        $request->twitchCategory = array_key_exists('twitchCategory', $data) && is_string($data['twitchCategory']) ? $data['twitchCategory'] : null;

        return $request;
    }
}
