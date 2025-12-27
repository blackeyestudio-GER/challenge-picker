<?php

namespace App\DTO\Request\Ruleset;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRulesetRequest
{
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(min: 3, max: 255)]
    public string $name;

    public ?string $description = null;

    /**
     * @var int[]
     */
    #[Assert\NotBlank(message: 'At least one game must be selected')]
    #[Assert\All([
        new Assert\Type(type: 'integer'),
        new Assert\Positive(),
    ])]
    public array $gameIds = [];

    /**
     * @var array<int, array{ruleId: int, isDefault: bool}>
     */
    #[Assert\All([
        new Assert\Collection([
            'ruleId' => [
                new Assert\Type(type: 'integer'),
                new Assert\Positive(),
            ],
            'isDefault' => [
                new Assert\Type(type: 'bool'),
            ],
        ]),
    ])]
    public array $ruleAssignments = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->name = is_string($data['name'] ?? null) ? $data['name'] : '';
        $request->description = is_string($data['description'] ?? null) ? $data['description'] : null;

        // Handle gameIds - support both single gameId and multiple gameIds
        if (isset($data['gameIds']) && is_array($data['gameIds'])) {
            $gameIds = [];
            foreach ($data['gameIds'] as $id) {
                if (is_numeric($id)) {
                    $gameIds[] = (int) $id;
                }
            }
            $request->gameIds = $gameIds;
        } elseif (isset($data['gameId']) && is_numeric($data['gameId'])) {
            $request->gameIds = [(int) $data['gameId']];
        }

        // Handle ruleAssignments
        if (isset($data['ruleAssignments']) && is_array($data['ruleAssignments'])) {
            foreach ($data['ruleAssignments'] as $assignment) {
                if (is_array($assignment)) {
                    $request->ruleAssignments[] = [
                        'ruleId' => (int) ($assignment['ruleId'] ?? 0),
                        'isDefault' => (bool) ($assignment['isDefault'] ?? false),
                    ];
                }
            }
        }

        return $request;
    }
}
