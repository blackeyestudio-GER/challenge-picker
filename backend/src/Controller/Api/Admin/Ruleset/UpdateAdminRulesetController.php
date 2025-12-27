<?php

namespace App\Controller\Api\Admin\Ruleset;

use App\DTO\Request\Ruleset\UpdateRulesetRequest;
use App\DTO\Response\Ruleset\RulesetResponse;
use App\Entity\RulesetRuleCard;
use App\Repository\GameRepository;
use App\Repository\RuleRepository;
use App\Repository\RulesetRepository;
use App\Service\RuleValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/rulesets/{id}', name: 'api_admin_rulesets_update', methods: ['PUT'])]
class UpdateAdminRulesetController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly GameRepository $gameRepository,
        private readonly RuleRepository $ruleRepository,
        private readonly RuleValidationService $ruleValidationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $ruleset = $this->rulesetRepository->find($id);

            if (!$ruleset) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULESET_NOT_FOUND',
                        'message' => 'Ruleset not found',
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
            $dto = UpdateRulesetRequest::fromArray($data);

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

            // Update basic fields
            if ($dto->name !== null) {
                $ruleset->setName($dto->name);
            }
            if ($dto->description !== null) {
                $ruleset->setDescription($dto->description);
            }

            // Update games if provided
            if ($dto->gameIds !== null) {
                if (empty($dto->gameIds)) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NO_GAMES_PROVIDED',
                            'message' => 'At least one game must be selected',
                        ],
                    ], Response::HTTP_BAD_REQUEST);
                }

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

                // Remove all existing games
                foreach ($ruleset->getGames()->toArray() as $game) {
                    $ruleset->removeGame($game);
                }

                // Add new games
                foreach ($games as $game) {
                    $ruleset->addGame($game);
                }
            }

            // Update rule assignments if provided
            if ($dto->ruleAssignments !== null) {
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

                // Validate default rules
                $rulesById = [];
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

                // Remove all existing rule assignments
                foreach ($ruleset->getRulesetRuleCards()->toArray() as $rulesetRuleCard) {
                    $this->entityManager->remove($rulesetRuleCard);
                }
                $ruleset->getRulesetRuleCards()->clear();

                // Add new rule assignments
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
                'message' => 'Ruleset updated successfully',
                'data' => ['ruleset' => RulesetResponse::fromEntity($ruleset)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to update ruleset: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
