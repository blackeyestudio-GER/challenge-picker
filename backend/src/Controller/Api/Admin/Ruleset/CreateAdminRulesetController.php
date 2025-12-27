<?php

namespace App\Controller\Api\Admin\Ruleset;

use App\DTO\Request\Ruleset\CreateRulesetRequest;
use App\DTO\Response\Ruleset\RulesetResponse;
use App\Entity\Ruleset;
use App\Entity\RulesetRuleCard;
use App\Repository\GameRepository;
use App\Repository\RuleRepository;
use App\Service\RuleValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/rulesets', name: 'api_admin_rulesets_create', methods: ['POST'])]
class CreateAdminRulesetController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly RuleRepository $ruleRepository,
        private readonly RuleValidationService $ruleValidationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
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

            /** @var array<string, mixed> $data */
            $dto = CreateRulesetRequest::fromArray($data);

            // Validate DTO
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_FAILED',
                        'message' => (string) $errors,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            if (empty($dto->gameIds)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NO_GAMES_PROVIDED',
                        'message' => 'At least one game must be selected',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Fetch games
            $games = $this->gameRepository->findBy(['id' => $dto->gameIds]);
            if (count($games) !== count($dto->gameIds)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'One or more games not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Fetch and validate rules if provided
            $rulesById = [];
            if (!empty($dto->ruleAssignments)) {
                $ruleIds = array_map(fn ($a) => $a['ruleId'], $dto->ruleAssignments);
                $rules = $this->ruleRepository->findBy(['id' => $ruleIds]);

                if (count($rules) !== count($ruleIds)) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'code' => 'RULE_NOT_FOUND',
                            'message' => 'One or more rules not found',
                        ],
                    ], Response::HTTP_NOT_FOUND);
                }

                // Build rules lookup and validate default rules
                foreach ($rules as $rule) {
                    $rulesById[$rule->getId()] = $rule;
                }

                foreach ($dto->ruleAssignments as $assignment) {
                    if ($assignment['isDefault']) {
                        $rule = $rulesById[$assignment['ruleId']];
                        if (!$this->ruleValidationService->canBeDefault($rule)) {
                            return $this->json([
                                'success' => false,
                                'error' => [
                                    'code' => 'INVALID_DEFAULT_RULE',
                                    'message' => sprintf(
                                        'Rule "%s" cannot be marked as default. Only permanent legendary rules (no duration/amount) can be default.',
                                        $rule->getName()
                                    ),
                                ],
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    }
                }
            }

            // Create ruleset
            $ruleset = new Ruleset();
            $ruleset->setName($dto->name);
            $ruleset->setDescription($dto->description);

            // Add all games to the ruleset
            foreach ($games as $game) {
                $ruleset->addGame($game);
            }

            $this->entityManager->persist($ruleset);

            // Create rule assignments
            if (!empty($dto->ruleAssignments)) {
                foreach ($dto->ruleAssignments as $assignment) {
                    $rule = $rulesById[$assignment['ruleId']];

                    $rulesetRuleCard = new RulesetRuleCard();
                    $rulesetRuleCard->setRuleset($ruleset);
                    $rulesetRuleCard->setRule($rule);
                    $rulesetRuleCard->setIsDefault($assignment['isDefault']);

                    $this->entityManager->persist($rulesetRuleCard);
                    $ruleset->addRulesetRuleCard($rulesetRuleCard);
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Ruleset created successfully',
                'data' => ['ruleset' => RulesetResponse::fromEntity($ruleset)],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            error_log('Failed to create ruleset: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
