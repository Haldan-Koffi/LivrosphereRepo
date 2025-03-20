<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function afficherCategories(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour accéder aux catégories.');
        }
        
        $categories = $em->getRepository(Categorie::class)->findAll();
        return $this->render('categorie/liste_categorie.html.twig', ['categories' => $categories]);
    }

    #[Route('/admin/nouvelle/categorie', name: 'nouvelle_categorie', methods: ['GET', 'POST'])]
    public function ajouterCategorie(Request $request, CsrfTokenManagerInterface $csrfTokenManager, EntityManagerInterface $em): Response
    {
        $csrfToken = $csrfTokenManager->getToken('nouvelle_categorie')->getValue();

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour créer une catégorie.');
        }

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('nouvelle_categorie', $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $nom = $request->request->get('nom');
            $file = $request->files->get('couverture_categorie');

            $categorie = new Categorie();
            $categorie->setNom($nom);
            $categorie->setDateCreation(new \DateTime());
            $categorie->setUtilisateur($user);

            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move($this->getParameter('upload_directory'), $fileName);
                    $categorie->setCouvertureCategorie($fileName);
                } catch (FileException $e) {
                    throw new \Exception('Erreur lors du téléchargement du fichier.');
                }
            }

            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/nouvelle_categorie.html.twig', [
            'csrf_token' => $csrfToken
        ]);
    }

    #[Route('/categorie/{id}/modification', name: 'modification_categorie', methods: ['GET', 'POST'])]
    public function modifierCategorie(Categorie $categorie, Request $request, CsrfTokenManagerInterface $csrfTokenManager, EntityManagerInterface $em): Response
    {
        $csrfToken = $csrfTokenManager->getToken('modification_categorie_' . $categorie->getId())->getValue();

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour modifier une catégorie.');
        }

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('modification_categorie_' . $categorie->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $nom = $request->request->get('nom');
            $file = $request->files->get('couvertureCategorie');

            $categorie->setNom($nom);
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move($this->getParameter('upload_directory'), $fileName);
                    $categorie->setCouvertureCategorie($fileName);
                } catch (FileException $e) {
                    throw new \Exception('Erreur lors du téléchargement du fichier.');
                }
            }
            $categorie->setDateCreation(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/modification.html.twig', [
            'categorie' => $categorie,
            'csrf_token' => $csrfToken
        ]);
    }

    #[Route('/categorie/{id}/supprimer', name: 'supprimer_categorie', methods: ['GET'])]
    public function supprimerCategorie(Categorie $categorie, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour supprimer une catégorie.');
        }

        if (!in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas l\'autorisation de supprimer cette catégorie.');
        }

        // Supprimer tous les livres associés à la catégorie, avec leurs dépendances
        foreach ($categorie->getLivres() as $livre) {
            foreach ($livre->getCommentaires() as $commentaire) {
                $em->remove($commentaire);
            }
            foreach ($livre->getInteractionJaimes() as $interaction) {
                $em->remove($interaction);
            }
            foreach ($livre->getRecommandations() as $recommandation) {
                $em->remove($recommandation);
            }
            $em->remove($livre);
        }

        $em->remove($categorie);
        $em->flush();

        return $this->redirectToRoute('app_categorie');
    }
}
