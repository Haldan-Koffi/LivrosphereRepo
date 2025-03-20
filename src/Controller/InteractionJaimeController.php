<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Utilisateur;
use App\Entity\InteractionJaime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/livre')]
class InteractionJaimeController extends AbstractController
{
    #[Route('/{id}/like', name: 'toggle_like', methods: ['POST'])]
    public function gererInteractionJaime(Livre $livre, EntityManagerInterface $entityManager): Response
    {
        
        $currentUtilisateur = $this->getUser();
        if (!$currentUtilisateur instanceof Utilisateur) {
            return $this->redirectToRoute('app_connexion');
        }

        
        $interactionRepo = $entityManager->getRepository(InteractionJaime::class);
        
        $existingLike = $interactionRepo->findByUserAndLivre($currentUtilisateur, $livre);

        if ($existingLike) {
            
            $entityManager->remove($existingLike);
            $entityManager->flush();
        } else {
            
            $like = new InteractionJaime();
            $like->setUtilisateur($currentUtilisateur);
            $like->setLivre($livre);
            $like->setDateJaime(new \DateTime());

            $entityManager->persist($like);
            $entityManager->flush();
        }

        return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
    }
}
