<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Request\Admin\CreateRuleRequest;
use App\DTO\Response\Admin\RuleMutationResponse;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/rules', name: 'api_admin_rules_create', methods: ['POST'])]
class CreateAdminRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleValidationService $validationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = CreateRuleRequest::fromArray($payloadData);
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

            $validationError = $this->validationService->validateRuleDifficultyLevels(
                $payload->ruleType,
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

            $rule = new Rule();
            $rule->setName($payload->name);
            $rule->setDescription($payload->description);
            $rule->setRuleType($payload->ruleType);
            $rule->setIconIdentifier($payload->iconIdentifier);

            foreach ($payload->difficultyLevels as $levelData) {
                $difficultyLevel = new RuleDifficultyLevel();
                $difficultyLevel->setDifficultyLevel($levelData['difficultyLevel']);
                $difficultyLevel->setDurationSeconds($levelData['durationSeconds']);
                $difficultyLevel->setAmount($levelData['amount']);
                $difficultyLevel->setDescription(null);
                $rule->addDifficultyLevel($difficultyLevel);
            }

            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            return $this->json(
                RuleMutationResponse::fromValues('Rule created successfully', RuleResponse::fromEntity($rule)),
                Response::HTTP_CREATED
            );

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
