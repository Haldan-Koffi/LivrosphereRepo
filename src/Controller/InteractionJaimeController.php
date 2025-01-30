<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Utilisateur;
use App\Service\InteractionJaimeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/livre')]
class InteractionJaimeController extends AbstractController
{
    #[Route('/{id}/like', name: 'toggle_like', methods: ['POST'])]
    public function toggleLike(
        Livre $livre,
        InteractionJaimeService $interactionJaimeService
    ): Response {
        $currentUtilisateur = $this->getUser();

        if (!$currentUtilisateur instanceof Utilisateur) {
            return $this->redirectToRoute('app_connexion');
        }

        $existingLike = $interactionJaimeService->hasLiked($currentUtilisateur, $livre);

        if ($existingLike) {
            $interactionJaimeService->removeLike($existingLike);
        } else {
            $interactionJaimeService->addLike($currentUtilisateur, $livre);
        }

        return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
    }
}
