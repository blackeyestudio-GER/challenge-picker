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
        $rulesetId = $request->query->get('rulesetId');
        
        if ($rulesetId) {
            // Find all rules that are associated with this ruleset
            $rules = $this->ruleRepository->findByRuleset((int)$rulesetId);
        } else {
            $rules = $this->ruleRepository->findBy([], ['name' => 'ASC']);
        }

        $ruleResponses = array_map(
            fn($rule) => RuleResponse::fromEntity($rule),
            $rules
        );

        return $this->json([
            'success' => true,
            'data' => ['rules' => $ruleResponses]
        ], Response::HTTP_OK);
    }
}

