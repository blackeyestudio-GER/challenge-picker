<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Repository\RuleRepository;
use App\Repository\RulesetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rules/{ruleId}/rulesets/{rulesetId}', name: 'api_admin_rules_remove_from_ruleset', methods: ['DELETE'])]
class RemoveRuleFromRulesetController extends AbstractController
{
    public function __construct(
        private readonly RuleRepository $ruleRepository,
        private readonly RulesetRepository $rulesetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $ruleId, int $rulesetId): JsonResponse
    {
        try {
            $rule = $this->ruleRepository->find($ruleId);
            if (!$rule) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULE_NOT_FOUND',
                        'message' => 'Rule not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $ruleset = $this->rulesetRepository->find($rulesetId);
            if (!$ruleset) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULESET_NOT_FOUND',
                        'message' => 'Ruleset not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Remove the rule from the ruleset if associated
            if ($rule->getRulesets()->contains($ruleset)) {
                $rule->removeRuleset($ruleset);
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Rule removed from ruleset successfully',
                'data' => ['rule' => RuleResponse::fromEntity($rule)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to remove rule from ruleset: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'REMOVE_FAILED',
                    'message' => 'Failed to remove rule from ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
