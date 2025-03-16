<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Service\CategorieService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;



class CategorieController extends AbstractController
{
    private CategorieService $categorieService;

    public function __construct(CategorieService $categorieService)
    {
        $this->categorieService = $categorieService;
    }

    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour créer une catégorie.');
        }
        
        $categories = $this->categorieService->getAllCategories();
        return $this->render('categorie/liste_categorie.html.twig', ['categories' => $categories]);
    }

    #[Route('/admin/nouvelle/categorie', name: 'nouvelle_categorie', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
    // Générer le token CSRF pour la création de catégorie
        $csrfToken = $csrfTokenManager->getToken('nouvelle_categorie')->getValue();

        // Récupérer l'utilisateur connecté depuis le contrôleur (hérité de AbstractController)
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour créer une catégorie.');
        }

        if ($request->isMethod('POST')) {
            // Vérification du token CSRF
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('nouvelle_categorie', $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            // Récupération des données du formulaire
            $nom = $request->request->get('nom');
            $file = $request->files->get('couverture_categorie');

            // Appel du service en passant l'utilisateur connecté
            $this->categorieService->createCategorie($nom, $file, $user);

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/nouvelle_categorie.html.twig', [
        'csrf_token' => $csrfToken
        ]);
    }


    #[Route('/categorie/{id}/modification', name: 'modification_categorie', methods: ['GET', 'POST'])]
    public function edit(Categorie $categorie, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Générer le token CSRF pour la modification de catégorie
        $csrfToken = $csrfTokenManager->getToken('modification_categorie_' . $categorie->getId())->getValue();

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour créer une catégorie.');
        }

        if ($request->isMethod('POST')) {
            // Vérification du token CSRF
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('modification_categorie_' . $categorie->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $nom = $request->request->get('nom');
            $file = $request->files->get('couvertureCategorie');

            $this->categorieService->updateCategorie($categorie, $nom, $file);

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/modification.html.twig', [
            'categorie' => $categorie,
            'csrf_token' => $csrfToken
        ]);
    }


    #[Route('/categorie/{id}/supprimer', name: 'supprimer_categorie', methods: ['GET'])]
    public function delete(Categorie $categorie): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        // $currentUser = $this->getUser();

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour créer une catégorie.');
        }

        // Vérifier que l'utilisateur est connecté et qu'il possède le rôle admin
        if (!$currentUser || !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas l\'autorisation de supprimer cette catégorie.');
        }

        $this->categorieService->deleteCategorie($categorie);
        return $this->redirectToRoute('app_categorie');
    }

}
