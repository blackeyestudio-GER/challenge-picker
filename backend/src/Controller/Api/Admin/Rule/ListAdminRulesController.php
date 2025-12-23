<?php

namespace App\Controller\Api\Admin\Rule;

use App\DTO\Response\Rule\RuleResponse;
use App\Repository\RuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rules', name: 'api_admin_rules_list', methods: ['GET'])]
class ListAdminRulesController extends AbstractController
{
    public function __construct(
        private readonly RuleRepository $ruleRepository
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(200, (int) $request->query->get('limit', 20))); // Default 20 per page
        $search = $request->query->get('search', '');

        $offset = ($page - 1) * $limit;

        if (!empty($search)) {
            // Search mode: return all matching rules
            $rules = $this->ruleRepository->searchRules($search, $limit, $offset);
            $total = $this->ruleRepository->countSearchResults($search);
        } else {
            // Browse mode: return paginated rules ordered by name
            $rules = $this->ruleRepository->findBy([], ['name' => 'ASC'], $limit, $offset);
            $total = $this->ruleRepository->count([]);
        }

        $ruleResponses = array_map(
            fn($rule) => RuleResponse::fromEntity($rule),
            $rules
        );

        return $this->json([
            'success' => true,
            'data' => [
                'rules' => $ruleResponses,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => (int) ceil($total / $limit)
                ]
            ]
        ], Response::HTTP_OK);
    }
}

