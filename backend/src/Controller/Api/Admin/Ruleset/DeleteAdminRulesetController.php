<?php

namespace App\Controller\Api\Admin\Ruleset;

use App\Repository\RulesetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rulesets/{id}', name: 'api_admin_rulesets_delete', methods: ['DELETE'])]
class DeleteAdminRulesetController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id): JsonResponse
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

            $this->entityManager->remove($ruleset);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Ruleset deleted successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to delete ruleset: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
