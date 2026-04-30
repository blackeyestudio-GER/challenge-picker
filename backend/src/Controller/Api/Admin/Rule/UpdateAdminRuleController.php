<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Request\Admin\UpdateRuleRequest;
use App\DTO\Response\Admin\RuleMutationResponse;
use App\DTO\Response\Rule\RuleResponse;
use App\Entity\RuleDifficultyLevel;
use App\Repository\RuleRepository;
use App\Service\RuleValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/rules/{id}', name: 'api_admin_rules_update', methods: ['PUT'])]
class UpdateAdminRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleRepository $ruleRepository,
        private readonly RuleValidationService $validationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
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

            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = UpdateRuleRequest::fromArray($payloadData);
            $errors = $this->validator->validate($payload);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => (string) $errors,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($payload->name !== null) {
                $rule->setName($payload->name);
            }
            if ($payload->hasDescription) {
                $rule->setDescription($payload->description);
            }
            if ($payload->ruleType !== null) {
                $rule->setRuleType($payload->ruleType);
            }

            if ($payload->hasIconIdentifier) {
                $rule->setIconIdentifier($payload->iconIdentifier);
            }

            if ($payload->hasDifficultyLevels && $payload->difficultyLevels !== null) {
                $resolvedRuleType = $payload->ruleType ?? $rule->getRuleType();
                if ($resolvedRuleType === null) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'code' => 'VALIDATION_ERROR',
                            'message' => 'Rule type is required when updating difficulty levels',
                        ],
                    ], Response::HTTP_BAD_REQUEST);
                }

                $validationError = $this->validationService->validateRuleDifficultyLevels(
                    $resolvedRuleType,
                    $payload->difficultyLevels
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

                $existingLevels = $rule->getDifficultyLevels()->toArray();
                foreach ($existingLevels as $existingLevel) {
                    $rule->removeDifficultyLevel($existingLevel);
                    $this->entityManager->remove($existingLevel);
                }

                $this->entityManager->flush();

                foreach ($payload->difficultyLevels as $levelData) {
                    $difficultyLevel = new RuleDifficultyLevel();
                    $difficultyLevel->setDifficultyLevel($levelData['difficultyLevel']);
                    $difficultyLevel->setDurationSeconds($levelData['durationSeconds']);
                    $difficultyLevel->setAmount($levelData['amount']);
                    $difficultyLevel->setDescription(null);
                    $rule->addDifficultyLevel($difficultyLevel);
                }
            }

            $this->entityManager->flush();

            return $this->json(
                RuleMutationResponse::fromValues('Rule updated successfully', RuleResponse::fromEntity($rule)),
                Response::HTTP_OK
            );

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
