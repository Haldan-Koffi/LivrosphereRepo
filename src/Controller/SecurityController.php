<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_connexion', methods: ['GET', 'POST'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError(); // Récupère l'erreur de dernière authentification, s'il y en a une (par exemple, mot de passe incorrect)
        $lastUsername = $authenticationUtils->getLastUsername(); // Récupère le dernier nom d'utilisateur utilisé lors de la tentative de connexion (en cas d'erreur, le nom d'utilisateur est déjà pré-rempli dans le formulaire)

        // Rend la vue 'security/login.html.twig' et passe les variables nécessaires (dernier nom d'utilisateur et erreur de connexion) à la vue pour l'affichage
        return $this->render('security/connexion.html.twig', [
            'last_username' => $lastUsername, // Transfert du dernier nom d'utilisateur à la vue pour l'affichage
            'error' => $error, // Transfert de l'erreur de connexion à la vue pour l'affichage
        ]);
    }

    #[Route('/logout', name: 'app_logout')] // Annotation définissant la route '/logout' pour gérer la déconnexion des utilisateurs
    public function logout(): void {}
}
