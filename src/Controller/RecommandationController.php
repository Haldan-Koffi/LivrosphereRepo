<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Recommandation;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecommandationController extends AbstractController
{
    #[Route('/livre/{id}/toggle-recommandation', name: 'toggle_recommandation', methods: ['POST'])]
    public function gererRecommandation(Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $currentUtilisateur = $this->getUser();

        if (!$currentUtilisateur instanceof Utilisateur) {
            return $this->redirectToRoute('app_connexion');
        }

        $recommandationRepo = $entityManager->getRepository(Recommandation::class);
        
        $existingRecommandation = $recommandationRepo->findByUserAndLivre($currentUtilisateur, $livre);

        if ($existingRecommandation) {
            
            $entityManager->remove($existingRecommandation);
            $entityManager->flush();
        } else {
            
            $recommandation = new Recommandation();
            $recommandation->setUtilisateur($currentUtilisateur);
            $recommandation->setLivre($livre);
            $recommandation->setDateRecommandation(new \DateTime());

            $entityManager->persist($recommandation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
    }
}
