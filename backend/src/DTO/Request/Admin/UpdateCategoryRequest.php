<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCategoryRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public ?string $description = null;
    public bool $hasDescription = false;

    /**
     * @var list<int>|null
     */
    public ?array $gameIds = null;
    public bool $hasGameIds = false;

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

        if (array_key_exists('gameIds', $data) && is_array($data['gameIds'])) {
            $request->hasGameIds = true;
            $gameIds = [];
            foreach ($data['gameIds'] as $gameId) {
                if (is_numeric($gameId)) {
                    $gameIds[] = (int) $gameId;
                }
            }
            $request->gameIds = $gameIds;
        }

        return $request;
    }
}
