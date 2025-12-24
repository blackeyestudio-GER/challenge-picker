<?php

namespace App\DTO\Response\Ruleset;

class RulesetListResponse
{
    public bool $success = true;

    /** @var RulesetResponse[] */
    public array $data = [];

    /**
     * @param RulesetResponse[] $rulesets
     */
    public static function fromRulesets(array $rulesets): self
    {
        $response = new self();
        $response->data = $rulesets;

        return $response;
    }
}
