<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Entity\Rule;
use App\Entity\RuleDifficultyLevel;
use App\Service\RuleValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rules', name: 'api_admin_rules_create', methods: ['POST'])]
class CreateAdminRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleValidationService $validationService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validate required fields
            if (empty($data['name']) || empty($data['ruleType']) || !isset($data['difficultyLevels'])) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Name, ruleType, and difficultyLevels are required',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate difficulty levels
            $validationError = $this->validationService->validateRuleDifficultyLevels(
                $data['ruleType'],
                $data['difficultyLevels']
            );

            if ($validationError) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => $validationError,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create the rule
            $rule = new Rule();
            $rule->setName($data['name']);
            $rule->setDescription($data['description'] ?? null);
            $rule->setRuleType($data['ruleType']);

            // Create difficulty levels
            foreach ($data['difficultyLevels'] as $levelData) {
                $difficultyLevel = new RuleDifficultyLevel();
                $difficultyLevel->setDifficultyLevel($levelData['difficultyLevel']);
                $difficultyLevel->setDurationSeconds($levelData['durationSeconds'] ?? null);
                $difficultyLevel->setAmount($levelData['amount'] ?? null);
                $difficultyLevel->setDescription(null); // Variants don't need individual descriptions
                $rule->addDifficultyLevel($difficultyLevel);
            }

            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rule created successfully',
                'data' => ['rule' => RuleResponse::fromEntity($rule)],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            error_log('Failed to create rule: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create rule: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
