<?php

namespace App\DTO\Request\Ruleset;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRulesetRequest
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    public ?string $description = null;

    /**
     * @var int[]|null
     */
    #[Assert\All([
        new Assert\Type(type: 'integer'),
        new Assert\Positive(),
    ])]
    public ?array $gameIds = null;

    /**
     * @var array<int, array{ruleId: int, isDefault: bool}>|null
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
    public ?array $ruleAssignments = null;

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
            $request->description = is_string($data['description']) ? $data['description'] : null;
        }

        // Handle gameIds
        if (isset($data['gameIds']) && is_array($data['gameIds'])) {
            $gameIds = [];
            foreach ($data['gameIds'] as $id) {
                if (is_numeric($id)) {
                    $gameIds[] = (int) $id;
                }
            }
            $request->gameIds = $gameIds;
        }

        // Handle ruleAssignments
        if (isset($data['ruleAssignments']) && is_array($data['ruleAssignments'])) {
            $request->ruleAssignments = [];
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
