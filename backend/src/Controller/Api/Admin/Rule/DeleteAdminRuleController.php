<?php

namespace App\Controller\Api\Admin\Rule;

use App\Repository\RuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rules/{id}', name: 'api_admin_rules_delete', methods: ['DELETE'])]
class DeleteAdminRuleController extends AbstractController
{
    public function __construct(
        private readonly RuleRepository $ruleRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(int $id): JsonResponse
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

            $this->entityManager->remove($rule);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rule deleted successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            error_log('Failed to delete rule: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete rule'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

