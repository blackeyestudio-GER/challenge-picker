<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRuleRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public ?string $description = null;
    public bool $hasDescription = false;

    public ?string $ruleType = null;

    public ?string $iconIdentifier = null;
    public bool $hasIconIdentifier = false;

    /**
     * @var list<array{difficultyLevel: int, durationSeconds: int|null, amount: int|null}>|null
     */
    public ?array $difficultyLevels = null;
    public bool $hasDifficultyLevels = false;

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

        if (isset($data['ruleType']) && is_string($data['ruleType'])) {
            $request->ruleType = $data['ruleType'];
        }

        if (array_key_exists('iconIdentifier', $data)) {
            $request->hasIconIdentifier = true;
            $request->iconIdentifier = is_string($data['iconIdentifier']) ? $data['iconIdentifier'] : null;
        }

        if (array_key_exists('difficultyLevels', $data) && is_array($data['difficultyLevels'])) {
            $request->hasDifficultyLevels = true;
            $request->difficultyLevels = [];

            foreach ($data['difficultyLevels'] as $levelData) {
                if (!is_array($levelData)) {
                    continue;
                }

                $difficultyLevel = $levelData['difficultyLevel'] ?? null;
                $durationSeconds = $levelData['durationSeconds'] ?? null;
                $amount = $levelData['amount'] ?? null;

                $request->difficultyLevels[] = [
                    'difficultyLevel' => is_numeric($difficultyLevel) ? (int) $difficultyLevel : 0,
                    'durationSeconds' => is_numeric($durationSeconds) ? (int) $durationSeconds : null,
                    'amount' => is_numeric($amount) ? (int) $amount : null,
                ];
            }
        }

        return $request;
    }
}
