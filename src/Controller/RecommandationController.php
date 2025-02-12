<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Utilisateur;
use App\Service\RecommandationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class RecommandationController extends AbstractController
{
    #[Route('/livre/{id}/toggle-recommandation', name: 'toggle_recommandation', methods: ['POST'])]
    public function toggleRecommandation(
        Livre $livre,
        RecommandationService $recommandationService
    ): Response {
        $currentUtilisateur = $this->getUser();

        if (!$currentUtilisateur instanceof Utilisateur) {
            return $this->redirectToRoute('app_connexion');
        }

        $existingRecommandation = $recommandationService->hasRecommended($currentUtilisateur, $livre);

        if ($existingRecommandation) {
            $recommandationService->removeRecommandation($existingRecommandation);
        } else {
            $recommandationService->addRecommandation($currentUtilisateur, $livre);
        }

        return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
    }
}
