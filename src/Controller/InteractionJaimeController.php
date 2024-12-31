<?php

namespace App\Controller;

use DateTime;
use App\Entity\Livre;
use App\Entity\InteractionJaime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/livre')]
class InteractionJaimeController extends AbstractController
{
    // #[Route('/interaction/jaime', name: 'app_interaction_jaime')]
    // public function index(): Response
    // {
    //     return $this->render('interaction_jaime/index.html.twig', [
    //         'controller_name' => 'InteractionJaimeController',
    //     ]);
    // }

    #[Route('/{id}/like', name: 'toggle_like', methods: ['POST'])]
    public function toggleLike(Livre $livre, EntityManagerInterface $em): Response
    {
        $currentUtilisateur = $this->getUser();

        if (!$currentUtilisateur) {
            return $this->redirectToRoute('app_connexion');
        }

        $interactionJaimeRepo = $em->getRepository(InteractionJaime::class);
        $existingLike = $interactionJaimeRepo->findByUserAndLivre($currentUtilisateur, $livre);

        if ($existingLike) {
            $em->remove($existingLike);
            $em->flush();

            return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
        }

        $like = new InteractionJaime();
        $like->setUtilisateur($currentUtilisateur);
        $like->setLivre($livre);
        $like->setDateJaime(new DateTime());

        $em->persist($like);
        $em->flush();

        return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
    }
}
