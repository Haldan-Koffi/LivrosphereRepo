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

        if (!$currentUtilisateur) {
            return $this->redirectToRoute('login');
        }

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

    
    #[Route('/inscription', name: 'utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, CsrfTokenManagerInterface $csrfTokenManager): Response
    {

        $csrfToken = $csrfTokenManager->getToken('utilisateur_new')->getValue();

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            // dd($token);
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_new', $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $utilisateur = new Utilisateur();
            $utilisateur->setEmail($request->request->get('email'));
            $utilisateur->setPseudonyme($request->request->get('pseudonyme'));

            $formMotDePasse = $request->request->get('mot_de_passe');
            
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

            $hashedMotDePasse = $passwordHasher->hashPassword($utilisateur, $formMotDePasse);
            $utilisateur->setMotDePasse($hashedMotDePasse);
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setDateInscription(new \DateTime());

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
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_modification_' . $utilisateur->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $utilisateur->setPseudonyme($request->request->get('pseudonyme'));
            $utilisateur->setEmail($request->request->get('email'));
            $em->flush();

            return $this->redirectToRoute('app_accueil');
        }

        $csrfToken = $csrfTokenManager->getToken('utilisateur_modification_' . $utilisateur->getId())->getValue();

        return $this->render('utilisateur/modification.html.twig', [
            'utilisateur' => $utilisateur,
            'csrf_token' => $csrfToken,
        ]);
    }

    #[Route('/utilisateur/{id}/modifier_mot_de_passe', name: 'utilisateur_modifier_mot_de_passe', methods: ['GET', 'POST'])]
    public function changePassword(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        
        if ($utilisateur !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ces informations.');
        }

        if ($request->isMethod('POST')) {
            
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
 
            if (!$passwordHasher->isPasswordValid($utilisateur, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->render('utilisateur/modifier_mot_de_passe.html.twig', [
                    'utilisateur' => $utilisateur,
                    'csrf_token' => $csrfTokenManager->getToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId())->getValue(),
                ]);
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->render('utilisateur/modifier_mot_de_passe.html.twig', [
                    'utilisateur' => $utilisateur,
                    'csrf_token' => $csrfTokenManager->getToken('utilisateur_modifier_mot_de_passe_' . $utilisateur->getId())->getValue(),
                ]);
            }

            $hashedNewPassword = $passwordHasher->hashPassword($utilisateur, $newPassword);
            $utilisateur->setMotDePasse($hashedNewPassword);

            $em->flush();

            return $this->redirectToRoute('mon_espace');
        }

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

        if (!$currentUser || (!in_array('ROLE_ADMIN', $currentUser->getRoles()) && $currentUser->getId() !== $utilisateur->getId())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission de supprimer cet utilisateur.');
        }

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

        $em->remove($utilisateur);
        $em->flush();

        if ($currentUser->getId() === $utilisateur->getId()) {
            $tokenStorage->setToken(null);
            $session->invalidate();
        }

        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/mon-espace', name: 'mon_espace', methods: ['GET'])]
    public function monEspace(LivreRepository $livreRepository): Response
    {
        $utilisateur = $this->getUser();

        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre espace.');
            return $this->redirectToRoute('app_connexion');
        }

        $livresAjoutes = $livreRepository->findBy(['utilisateur' => $utilisateur]);

        return $this->render('utilisateur/mon_espace.html.twig', [
            'livres' => $livresAjoutes,
        ]);
    }
}