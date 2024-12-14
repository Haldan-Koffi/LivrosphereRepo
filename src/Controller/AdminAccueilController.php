<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminAccueilController extends AbstractController
{
    #[Route('/admin/accueil', name: 'app_admin_accueil')]
    public function index(): Response
    {
        return $this->render('admin_accueil/index.html.twig', [
            'controller_name' => 'AdminAccueilController',
        ]);
    }
}
