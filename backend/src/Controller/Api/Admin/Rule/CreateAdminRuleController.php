<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Entity\Rule;
use App\Repository\RulesetRepository;
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
        private readonly RulesetRepository $rulesetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Create the rule
            $rule = new Rule();
            $rule->setName($data['name']);
            $rule->setDescription($data['description'] ?? null);
            $rule->setDurationMinutes($data['durationMinutes'] ?? 60);

            // Associate with rulesets if provided
            if (!empty($data['rulesetIds']) && is_array($data['rulesetIds'])) {
                foreach ($data['rulesetIds'] as $rulesetId) {
                    $ruleset = $this->rulesetRepository->find($rulesetId);
                    if ($ruleset) {
                        $rule->addRuleset($ruleset);
                    }
                }
            }

            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rule created successfully',
                'data' => ['rule' => RuleResponse::fromEntity($rule)]
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            error_log('Failed to create rule: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create rule'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

