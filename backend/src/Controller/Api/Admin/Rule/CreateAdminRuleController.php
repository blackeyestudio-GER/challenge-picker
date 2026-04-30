<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Entity\Rule;
use App\Entity\RuleDifficultyLevel;
use App\Service\ArrayTypeHelper;
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
            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_REQUEST',
                        'message' => 'Invalid request body',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            /* @var array<string, mixed> $data */
            // Validate required fields
            try {
                $name = ArrayTypeHelper::getString($data, 'name');
                $ruleType = ArrayTypeHelper::getString($data, 'ruleType');
                $difficultyLevels = ArrayTypeHelper::getArray($data, 'difficultyLevels');
            } catch (\InvalidArgumentException $e) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => $e->getMessage(),
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate difficulty levels
            $validationError = $this->validationService->validateRuleDifficultyLevels(
                $ruleType,
                $difficultyLevels
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
            $rule->setName($name);
            $rule->setDescription(ArrayTypeHelper::tryGetString($data, 'description'));
            $rule->setRuleType($ruleType);

            // Set icon identifier if provided (styling is now in DesignSet)
            $iconIdentifier = ArrayTypeHelper::tryGetString($data, 'iconIdentifier');
            if ($iconIdentifier !== null) {
                $rule->setIconIdentifier($iconIdentifier);
            }

            // Create difficulty levels
            /** @var array<string, mixed> $levelData */
            foreach ($difficultyLevels as $levelData) {
                if (!is_array($levelData)) {
                    continue;
                }
                $difficultyLevel = new RuleDifficultyLevel();
                $difficultyLevel->setDifficultyLevel(ArrayTypeHelper::getInt($levelData, 'difficultyLevel'));
                $difficultyLevel->setDurationSeconds(ArrayTypeHelper::tryGetInt($levelData, 'durationSeconds'));
                $difficultyLevel->setAmount(ArrayTypeHelper::tryGetInt($levelData, 'amount'));
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
