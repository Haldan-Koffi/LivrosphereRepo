<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccueilController extends AbstractController
{
    // #[Route('/accueil', name: 'app_accueil')]
    // public function index(): Response
    // {
    //     return $this->render('accueil/index.html.twig', [
    //         'controller_name' => 'AccueilController',
    //     ]);
    // }

    #[Route('/', name: 'app_accueil')]
    public function indexLivre(LivreRepository $livreRepository): Response
    {
        $derniersLivres = $livreRepository->findBy([], ['date_ajout' => 'DESC'], 3);


        if (empty($derniersLivres)) {
            $this->addFlash('info', 'Aucun livre disponible pour le moment.');
        }


        return $this->render('accueil/index.html.twig', [
            'derniersLivres' => $derniersLivres,
        ]);
    }

}
