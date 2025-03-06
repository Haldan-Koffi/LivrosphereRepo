<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateurs', name: 'app_utilisateur', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();

        return $this->render('utilisateur/liste_utilisateur.html.twig', [
            'utilisateurs' => $utilisateurs]);
    }

    #[Route('/utilisateurs/information', name: 'utilisateur_info', methods: ['GET'])]
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
                'pseudonyme' => $currentUtilisateur->getPseudonyme(),
                'email' => $currentUtilisateur->getEmail(),
                'roles' => $currentUtilisateur->getRoles(),
            ]
        ]);
    }

    // Injecte le service ValidatorInterface dans la méthode
    #[Route('/inscription', name: 'utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, CsrfTokenManagerInterface $csrfTokenManager): Response
    {

        $csrfToken = $csrfTokenManager->getToken('utilisateur_new')->getValue();

        if ($request->isMethod('POST')) {
            // Vérification du token CSRF
            $token = $request->request->get('_csrf_token');
            // dd($token);
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_new', $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            // Traitement des données
            $utilisateur = new Utilisateur();
            // $utilisateur->setNom($request->request->get('nom'));
            // $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setEmail($request->request->get('email'));
            $utilisateur->setPseudonyme($request->request->get('pseudonyme'));

            $formMotDePasse = $request->request->get('mot_de_passe');
            
            // Vérifie les contraintes de validation
            $utilisateur->setMotDePasse($formMotDePasse);
            $errors = $validator->validate($utilisateur);
            
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->render('utilisateur/new.html.twig', [
                    'errors' => $errorMessages
                ]);
            }

            // Hashage et persistance si validation OK
            $hashedMotDePasse = $passwordHasher->hashPassword($utilisateur, $formMotDePasse);
            $utilisateur->setMotDePasse($hashedMotDePasse);
            $utilisateur->setRoles(['ROLE_USER']);

            $em->persist($utilisateur);
            $em->flush();

            return $this->redirectToRoute('app_accueil');
        }
        return $this->render('utilisateur/new.html.twig', [
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/utilisateur/{id}/modification', name: 'utilisateur_modification', methods: ['GET', 'POST'])]
    public function edit(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if ($request->isMethod('POST')) {
            // Vérification du token CSRF
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_modification_' . $utilisateur->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            // Traitement des modifications
            // $utilisateur->setNom($request->request->get('nom'));
            // $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setPseudonyme($request->request->get('pseudonyme'));
            $utilisateur->setEmail($request->request->get('email'));
            $em->flush();

            return $this->redirectToRoute('app_accueil');
        }

        // Génération du token CSRF
        $csrfToken = $csrfTokenManager->getToken('utilisateur_modification_' . $utilisateur->getId())->getValue();

        return $this->render('utilisateur/modification.html.twig', [
            'utilisateur' => $utilisateur,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/utilisateur/{id}/modifier_mot_de_passe', name: 'utilisateur_modifier_mot_de_passe', methods: ['GET', 'POST'])]
    public function changePassword(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifier si l'utilisateur connecté est bien celui qu'on veut modifier
        if ($utilisateur !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ces informations.');
        }

        if ($request->isMethod('POST')) {
            // Vérification du token CSRF
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            // Traitement du mot de passe actuel
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            // Vérification du mot de passe actuel
            if (!$passwordHasher->isPasswordValid($utilisateur, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->render('utilisateur/modifier_mot_de_passe.html.twig', [
                    'utilisateur' => $utilisateur,
                    'csrf_token' => $csrfTokenManager->getToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId())->getValue(),
                ]);
            }

            // Vérification de la correspondance des nouveaux mots de passe
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->render('utilisateur/modifier_mot_de_passe.html.twig', [
                    'utilisateur' => $utilisateur,
                    'csrf_token' => $csrfTokenManager->getToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId())->getValue(),
                ]);
            }

            // Hashage du nouveau mot de passe et mise à jour de l'utilisateur
            $hashedNewPassword = $passwordHasher->hashPassword($utilisateur, $newPassword);
            $utilisateur->setMotDePasse($hashedNewPassword);

            // Sauvegarde des modifications dans la base de données
            $em->flush();

            // Redirection après modification
            return $this->redirectToRoute('mon_espace');
        }

        // Génération du token CSRF pour sécuriser le formulaire
        $csrfToken = $csrfTokenManager->getToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId())->getValue();

        return $this->render('utilisateur/modifier_mot_de_passe.html.twig', [
            'utilisateur' => $utilisateur,
            'csrf_token' => $csrfToken,
        ]);
    }

    
    #[Route('/utilisateur/{id}/supprimer', name: 'utilisateur_supprimer', methods: ['GET'])]
    public function delete(Utilisateur $utilisateur, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, SessionInterface $session): Response 
    {
        $currentUser = $this->getUser();

        // Vérifier que l'utilisateur est connecté et qu'il est soit admin, soit lui-même
        if (!$currentUser || (!in_array('ROLE_ADMIN', $currentUser->getRoles()) && $currentUser->getId() !== $utilisateur->getId())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission de supprimer cet utilisateur.');
        }

        // Suppression des entités liées AVANT de supprimer l'utilisateur
        foreach ($utilisateur->getLivres() as $livre) {
            $em->remove($livre);
        }
        foreach ($utilisateur->getCommentaires() as $commentaire) {
            $em->remove($commentaire);
        }
        foreach ($utilisateur->getRecommandations() as $recommandation) {
            $em->remove($recommandation);
        }
        foreach ($utilisateur->getInteractionJaimes() as $interaction) {
            $em->remove($interaction);
        }

        // Suppression de l'utilisateur
        $em->remove($utilisateur);
        $em->flush();

        // Déconnecter l'utilisateur s'il supprime son propre compte
        if ($currentUser->getId() === $utilisateur->getId()) {
            $tokenStorage->setToken(null); // Supprime le token d'authentification
            $session->invalidate(); // Détruit la session
        }

        // Redirection après suppression
        return $this->redirectToRoute('app_accueil');
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