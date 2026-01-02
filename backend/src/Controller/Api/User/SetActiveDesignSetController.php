<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users/me/active-design-set', name: 'api_user_set_active_design_set', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class SetActiveDesignSetController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DesignSetRepository $designSetRepository,
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $designSetId = $data['designSetId'] ?? null;

        // Allow null to clear the active design (use default)
        if ($designSetId === null) {
            $user->setActiveDesignSetId(null);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'message' => 'Active design set cleared',
                ],
            ], Response::HTTP_OK);
        }

        // Validate design set exists
        $designSet = $this->designSetRepository->find($designSetId);
        if (!$designSet) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DESIGN_SET_NOT_FOUND',
                    'message' => 'Design set not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if user has access (free or purchased)
        $isFree = $designSet->isFree();
        $userDesignSets = $this->userDesignSetRepository->findByUser($user->getUuid());
        $hasPurchased = false;
        foreach ($userDesignSets as $uds) {
            if ($uds->getDesignSet()->getId() === $designSetId) {
                $hasPurchased = true;
                break;
            }
        }

        if (!$isFree && !$hasPurchased) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'You do not have access to this design set',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Set active design
        $user->setActiveDesignSetId($designSetId);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'designSetId' => $designSetId,
                'message' => 'Active design set updated',
            ],
        ], Response::HTTP_OK);
    }
}
