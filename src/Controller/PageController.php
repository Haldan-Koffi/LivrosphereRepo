<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/a_propos', name: 'app_a_propos')]
    public function indexap(): Response
    {
        return $this->render('page/a_propos.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/conditions_generales_d_utilisation', name: 'app_cgu')]
    public function indexcgu(): Response
    {
        return $this->render('page/cgu.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    #[Route('/mentions_legales', name: 'app_mentions_legales')]
    public function indexml(): Response
    {
        return $this->render('page/mentions_legales.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
