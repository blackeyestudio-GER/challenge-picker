<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRuleRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name = '';

    public ?string $description = null;

    #[Assert\NotBlank]
    public string $ruleType = '';

    public ?string $iconIdentifier = null;

    /**
     * @var list<array{difficultyLevel: int, durationSeconds: int|null, amount: int|null}>
     */
    public array $difficultyLevels = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->name = is_string($data['name'] ?? null) ? $data['name'] : '';
        $request->description = array_key_exists('description', $data) && is_string($data['description']) ? $data['description'] : null;
        $request->ruleType = is_string($data['ruleType'] ?? null) ? $data['ruleType'] : '';
        $request->iconIdentifier = array_key_exists('iconIdentifier', $data) && is_string($data['iconIdentifier']) ? $data['iconIdentifier'] : null;

        if (isset($data['difficultyLevels']) && is_array($data['difficultyLevels'])) {
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
