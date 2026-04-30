<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateGameRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public ?string $description = null;
    public bool $hasDescription = false;

    public ?string $image = null;
    public bool $hasImage = false;

    public ?bool $isCategoryRepresentative = null;
    /**
     * @var list<int>|null
     */
    public ?array $categoryIds = null;
    public bool $hasCategoryIds = false;

    public ?string $steamLink = null;
    public bool $hasSteamLink = false;

    public ?string $epicLink = null;
    public bool $hasEpicLink = false;

    public ?string $gogLink = null;
    public bool $hasGogLink = false;

    public ?string $twitchCategory = null;
    public bool $hasTwitchCategory = false;

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

        if (array_key_exists('image', $data)) {
            $request->hasImage = true;
            $request->image = is_string($data['image']) ? $data['image'] : null;
        }

        if (array_key_exists('isCategoryRepresentative', $data) && is_bool($data['isCategoryRepresentative'])) {
            $request->isCategoryRepresentative = $data['isCategoryRepresentative'];
        }

        if (array_key_exists('categoryIds', $data) && is_array($data['categoryIds'])) {
            $request->hasCategoryIds = true;
            $categoryIds = [];
            foreach ($data['categoryIds'] as $categoryId) {
                if (is_numeric($categoryId)) {
                    $categoryIds[] = (int) $categoryId;
                }
            }
            $request->categoryIds = $categoryIds;
        }

        if (array_key_exists('steamLink', $data)) {
            $request->hasSteamLink = true;
            $request->steamLink = is_string($data['steamLink']) ? $data['steamLink'] : null;
        }

        if (array_key_exists('epicLink', $data)) {
            $request->hasEpicLink = true;
            $request->epicLink = is_string($data['epicLink']) ? $data['epicLink'] : null;
        }

        if (array_key_exists('gogLink', $data)) {
            $request->hasGogLink = true;
            $request->gogLink = is_string($data['gogLink']) ? $data['gogLink'] : null;
        }

        if (array_key_exists('twitchCategory', $data)) {
            $request->hasTwitchCategory = true;
            $request->twitchCategory = is_string($data['twitchCategory']) ? $data['twitchCategory'] : null;
        }

        return $request;
    }
}
