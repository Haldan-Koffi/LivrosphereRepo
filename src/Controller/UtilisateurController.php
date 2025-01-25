<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UtilisateurController extends AbstractController
{
    #[Route('/utilisateurs', name: 'app_utilisateur', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();

        return $this->render('utilisateur/liste_utilisateur.html.twig', [
            'utilisateurs' => $utilisateurs]);
    }

    #[Route('/utilisateurs/info', name: 'utilisateur_info', methods: ['GET'])]
    public function show(): Response
    {
        $currentUtilisateur = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$currentUtilisateur) {
            return $this->redirectToRoute('login'); // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
        }

        // Afficher les données de l'utilisateur connecté
        return $this->render('utilisateur/info.html.twig', [
            'utilisateur' => [
                'id' => $currentUtilisateur->getId(),
                'nom' => $currentUtilisateur->getNom(),
                'prenom' => $currentUtilisateur->getPrenom(),
                'email' => $currentUtilisateur->getEmail(),
                'roles' => $currentUtilisateur->getRoles(),
            ]
        ]);
    }

    #[Route('/inscription', name: 'utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setEmail($request->request->get('email'));
            $utilisateur->setPseudonyme($request->request->get('pseudonyme'));
            $formMotDePasse = $request->request->get('mot_de_passe');
            $hashedMotDePasse = $passwordHasher->hashPassword($utilisateur, $formMotDePasse);
            $utilisateur->setMotDePasse($hashedMotDePasse);
            $utilisateur->setRoles(['ROLE_USER']);

            $em->persist($utilisateur);
            $em->flush();

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('utilisateur/new.html.twig');
    }

    #[Route('utilisateur/{id}/modification', name: 'utilisateur_modification', methods: ['GET', 'POST'])]
    public function edit(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setPseudonyme($request->request->get('pseudonyme'));
            $utilisateur->setEmail($request->request->get('email'));
            $em->flush();

            return $this->redirectToRoute('app_accueil');
        }
        return $this->render('utilisateur/modification.html.twig', ['utilisateur' => $utilisateur]);        
    }

    
    #[Route('utilisateurs/{id}/supprimer', name: 'utilisateur_supprimer', methods: ['GET'])] // La route '/{id}/delete' permet de supprimer un utilisateur
    public function delete(Utilisateur $utilisateur, EntityManagerInterface $em): Response // La méthode delete() permet de supprimer un utilisateur existant
    {
        $em->remove($utilisateur); // Supprime l'utilisateur de la base de données
        $em->flush(); // Sauvegarde la suppression dans la base de données

        return $this->redirectToRoute('app_utilisateur'); // Redirige vers la liste des utilisateurs après suppression
    }
    #[Route('/mon-espace', name: 'mon_espace', methods: ['GET'])]
    public function monEspace(LivreRepository $livreRepository): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $utilisateur = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre espace.');
            return $this->redirectToRoute('app_connexion'); // Redirection vers la page de connexion
        }

        // Récupérer les livres ajoutés par l'utilisateur
        $livresAjoutes = $livreRepository->findBy(['utilisateur' => $utilisateur]);

        // Rendre la vue du tableau de bord utilisateur
        return $this->render('utilisateur/mon_espace.html.twig', [
            'livres' => $livresAjoutes,
        ]);
    }
}