<?php

namespace App\Controller\Api\Admin\Shop;

use App\Entity\UserDesignSet;
use App\Repository\DesignSetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/shop/gift-design-set', name: 'api_admin_shop_gift_design_set', methods: ['POST'])]
class GiftDesignSetController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly DesignSetRepository $designSetRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['userIdentifier'], $data['designSetId'])) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Missing required fields: userIdentifier, designSetId'],
            ], 400);
        }

        $userIdentifier = (string) $data['userIdentifier'];
        $designSetId = (int) $data['designSetId'];

        // Find user by email or Discord ID
        $user = $this->userRepository->findOneBy(['email' => $userIdentifier])
            ?? $this->userRepository->findOneBy(['discordId' => $userIdentifier]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'User not found'],
            ], 404);
        }

        // Find design set
        $designSet = $this->designSetRepository->find($designSetId);
        if (!$designSet) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Design set not found'],
            ], 404);
        }

        // Check if user already owns this design set
        $existingOwnership = $this->entityManager->getRepository(UserDesignSet::class)->findOneBy([
            'userUuid' => $user->getUuid(),
            'designSet' => $designSet,
        ]);

        if ($existingOwnership) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'User already owns this design set'],
            ], 400);
        }

        // Create ownership record
        $userDesignSet = new UserDesignSet();
        $userDesignSet->setUserUuid($user->getUuid());
        $userDesignSet->setDesignSet($designSet);
        $userDesignSet->setPricePaid('0.00'); // Gifted
        $userDesignSet->setCurrency('USD');

        $this->entityManager->persist($userDesignSet);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'message' => sprintf('Design set "%s" gifted to %s', $designSet->getName() ?? 'Unknown', $user->getUsername() ?? 'Unknown'),
            ],
        ]);
    }
}
