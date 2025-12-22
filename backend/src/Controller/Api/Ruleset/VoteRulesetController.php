<?php

namespace App\Controller\Api\Ruleset;

use App\Entity\RulesetVote;
use App\Repository\RulesetRepository;
use App\Repository\RulesetVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/rulesets/{rulesetId}/vote', name: 'api_ruleset_vote', methods: ['POST'])]
class VoteRulesetController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly RulesetVoteRepository $voteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Vote for a ruleset
     * Supports upvotes (1) and downvotes (-1)
     * Clicking the same vote type again removes the vote
     */
    public function __invoke(
        int $rulesetId,
        Request $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            // Parse request body
            $data = json_decode($request->getContent(), true);
            $voteType = $data['voteType'] ?? 1; // Default to upvote

            // Validate vote type
            if (!in_array($voteType, [1, -1])) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_VOTE_TYPE',
                        'message' => 'Vote type must be 1 (upvote) or -1 (downvote)'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $ruleset = $this->rulesetRepository->find($rulesetId);
            
            if (!$ruleset) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULESET_NOT_FOUND',
                        'message' => 'Ruleset not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if user already voted
            $existingVote = $this->voteRepository->findVote($user, $ruleset);
            
            if ($existingVote) {
                // If clicking the same vote type, remove the vote
                if ($existingVote->getVoteType() === $voteType) {
                    $this->entityManager->remove($existingVote);
                    $this->entityManager->flush();

                    $voteCount = $this->voteRepository->getVoteCount($ruleset);

                    return $this->json([
                        'success' => true,
                        'message' => 'Vote removed successfully',
                        'data' => [
                            'voteCount' => $voteCount,
                            'userVoted' => false,
                            'userVoteType' => null
                        ]
                    ], Response::HTTP_OK);
                } else {
                    // If clicking a different vote type, update the vote
                    $existingVote->setVoteType($voteType);
                    $this->entityManager->flush();

                    $voteCount = $this->voteRepository->getVoteCount($ruleset);

                    return $this->json([
                        'success' => true,
                        'message' => 'Vote updated successfully',
                        'data' => [
                            'voteCount' => $voteCount,
                            'userVoted' => true,
                            'userVoteType' => $voteType
                        ]
                    ], Response::HTTP_OK);
                }
            }

            // Create new vote
            $vote = new RulesetVote();
            $vote->setUser($user);
            $vote->setRuleset($ruleset);
            $vote->setVoteType($voteType);

            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            // Get updated vote count
            $voteCount = $this->voteRepository->getVoteCount($ruleset);

            return $this->json([
                'success' => true,
                'message' => 'Vote added successfully',
                'data' => [
                    'voteCount' => $voteCount,
                    'userVoted' => true,
                    'userVoteType' => $voteType
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            error_log('Vote failed: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VOTE_FAILED',
                    'message' => 'Failed to add vote',
                    'debug' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

