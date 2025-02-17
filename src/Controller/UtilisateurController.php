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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

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

    // Injecte le service ValidatorInterface dans la méthode
    #[Route('/inscription', name: 'utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if ($request->isMethod('POST')) {
            // Vérification du token CSRF
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_new', $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            // Traitement des données
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
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

        // Génération du token CSRF
        $csrfToken = $csrfTokenManager->getToken('utilisateur_new')->getValue();

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
            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
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


    
    #[Route('/utilisateurs/{id}/supprimer', name: 'utilisateur_supprimer', methods: ['GET'])] // La route '/{id}/delete' permet de supprimer un utilisateur
    public function delete(Utilisateur $utilisateur, EntityManagerInterface $em): Response // La méthode delete() permet de supprimer un utilisateur existant
    {
        $em->remove($utilisateur); // Supprime l'utilisateur de la base de données
        $em->flush(); // Sauvegarde la suppression dans la base de données

        return $this->redirectToRoute('app_accueil'); // Redirige vers la liste des utilisateurs après suppression
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