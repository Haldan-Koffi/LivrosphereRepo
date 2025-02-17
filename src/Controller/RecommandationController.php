<?php

namespace App\Controller;

use DateTime;
use App\Entity\Livre;
use App\Entity\Recommandation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/livre')]
class RecommandationController extends AbstractController
{
    // #[Route('/recommandation', name: 'app_recommandation')]
    // public function index(): Response
    // {
    //     return $this->render('recommandation/index.html.twig', [
    //         'controller_name' => 'RecommandationController',
    //     ]);
    // }

    #[Route('/{id}/toggle-recommandation', name: 'toggle_recommandation', methods: ['POST'])]
    public function toggleRecommandation(Livre $livre, EntityManagerInterface $em): Response
    {
        $currentUtilisateur = $this->getUser();

        if (!$currentUtilisateur) {
            return $this->redirectToRoute('app_connexion'); // Redirection si l'utilisateur n'est pas connecté
        }

        // Vérifier si l'utilisateur a déjà recommandé ce livre
        $recommandationRepo = $em->getRepository(Recommandation::class);
        $existingRecommandation = $recommandationRepo->findByUserAndLivre($currentUtilisateur, $livre);

        if ($existingRecommandation) {
            // Si une recommandation existe, la supprimer
            $em->remove($existingRecommandation);
            $em->flush();


            return $this->redirectToRoute('livre_info', ['id' => $livre->getId()]);
        }
            // Sinon, créer une nouvelle recommandation
        $Recommandation = new Recommandation();
        $Recommandation->setUtilisateur($currentUtilisateur);
        $Recommandation->setLivre($livre);
        $Recommandation->setDateRecommandation(new DateTime());

        $em->persist($Recommandation);
        $em->flush();

        return $this->redirectToRoute('livre_info', ['id' => $livre->getId()]);
    }
}
