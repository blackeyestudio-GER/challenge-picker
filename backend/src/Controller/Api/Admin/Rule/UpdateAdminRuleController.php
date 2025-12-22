<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Repository\RuleRepository;
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
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $rule = $this->ruleRepository->find($id);
            
            if (!$rule) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULE_NOT_FOUND',
                        'message' => 'Rule not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $rule->setName($data['name']);
            }
            if (array_key_exists('description', $data)) {
                $rule->setDescription($data['description']);
            }
            if (isset($data['durationMinutes'])) {
                $rule->setDurationMinutes($data['durationMinutes']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rule updated successfully',
                'data' => ['rule' => RuleResponse::fromEntity($rule)]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            error_log('Failed to update rule: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update rule'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

