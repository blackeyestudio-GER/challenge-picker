<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Entity\RuleDifficultyLevel;
use App\Repository\RuleRepository;
use App\Service\ArrayTypeHelper;
use App\Service\RuleValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rules/{id}', name: 'api_admin_rules_update', methods: ['PUT'])]
class UpdateAdminRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleRepository $ruleRepository,
        private readonly RuleValidationService $validationService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $rule = $this->ruleRepository->find($id);

            if (!$rule) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULE_NOT_FOUND',
                        'message' => 'Rule not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

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

            /** @var array<string, mixed> $data */
            if (isset($data['name'])) {
                $rule->setName(ArrayTypeHelper::getString($data, 'name'));
            }
            if (array_key_exists('description', $data)) {
                $rule->setDescription(ArrayTypeHelper::tryGetString($data, 'description'));
            }
            if (isset($data['ruleType'])) {
                $rule->setRuleType(ArrayTypeHelper::getString($data, 'ruleType'));
            }

            // Update icon identifier if provided (styling is now in DesignSet)
            if (array_key_exists('iconIdentifier', $data)) {
                $rule->setIconIdentifier(ArrayTypeHelper::tryGetString($data, 'iconIdentifier'));
            }

            // Update difficulty levels if provided
            if (isset($data['difficultyLevels'])) {
                $difficultyLevels = ArrayTypeHelper::getArray($data, 'difficultyLevels');
                // Validate difficulty levels
                $validationError = $this->validationService->validateRuleDifficultyLevels(
                    isset($data['ruleType']) ? ArrayTypeHelper::getString($data, 'ruleType') : $rule->getRuleType(),
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

                // Remove existing difficulty levels
                $existingLevels = $rule->getDifficultyLevels()->toArray();
                foreach ($existingLevels as $existingLevel) {
                    $rule->removeDifficultyLevel($existingLevel);
                    $this->entityManager->remove($existingLevel);
                }

                // Flush removals before adding new ones to avoid unique constraint violations
                $this->entityManager->flush();

                // Add new difficulty levels
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
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rule updated successfully',
                'data' => ['rule' => RuleResponse::fromEntity($rule)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to update rule: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update rule: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
