<?php

namespace App\Controller;

use App\Service\MongoDBService;
use App\Repository\LivreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccueilController extends AbstractController
{

    #[Route('/', name: 'app_accueil')]
    public function afficherLivres(LivreRepository $livreRepository, MongoDBService $mongoDBService): Response
    {
        $mongoDBService->insertVisit('/');
        $derniersLivres = $livreRepository->findBy([], ['date_ajout' => 'DESC'], 3);


        if (empty($derniersLivres)) {
            $this->addFlash('info', 'Aucun livre disponible pour le moment.');
        }

        return $this->render('accueil/index.html.twig', [
            'derniersLivres' => $derniersLivres,
        ]);
    }

}
