<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Entity\RulesetRuleCard;
use App\Repository\RuleRepository;
use App\Repository\RulesetRepository;
use App\Repository\RulesetRuleCardRepository;
use App\Repository\TarotCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rules/{ruleId}/rulesets/{rulesetId}', name: 'api_admin_rules_add_to_ruleset', methods: ['POST'])]
class AddRuleToRulesetController extends AbstractController
{
    public function __construct(
        private readonly RuleRepository $ruleRepository,
        private readonly RulesetRepository $rulesetRepository,
        private readonly RulesetRuleCardRepository $rulesetRuleCardRepository,
        private readonly TarotCardRepository $tarotCardRepository,
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

            // Check if rule is already in ruleset
            $existingCard = $this->rulesetRuleCardRepository->findOneBy([
                'ruleset' => $ruleset,
                'rule' => $rule,
            ]);

            if (!$existingCard) {
                // Find a tarot card for this rule (use first available or default)
                $tarotCard = $this->tarotCardRepository->findOneBy(['identifier' => 'the_fool']);
                if (!$tarotCard) {
                    // Fallback: get any tarot card
                    $tarotCards = $this->tarotCardRepository->findAllOrdered();
                    $tarotCard = $tarotCards[0] ?? null;
                }

                if (!$tarotCard) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'code' => 'TAROT_CARD_NOT_FOUND',
                            'message' => 'No tarot cards available',
                        ],
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                // Create RulesetRuleCard to associate rule with ruleset
                $rulesetRuleCard = new RulesetRuleCard();
                $rulesetRuleCard->setRuleset($ruleset);
                $rulesetRuleCard->setRule($rule);
                $rulesetRuleCard->setTarotCard($tarotCard);
                $rulesetRuleCard->setPosition(0); // Will be set properly later
                $rulesetRuleCard->setIsDefault(false);

                $this->entityManager->persist($rulesetRuleCard);
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Rule added to ruleset successfully',
                'data' => ['rule' => RuleResponse::fromEntity($rule)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to add rule to ruleset: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'ADD_FAILED',
                    'message' => 'Failed to add rule to ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
