<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Service\CategorieService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    private CategorieService $categorieService;

    public function __construct(CategorieService $categorieService)
    {
        $this->categorieService = $categorieService;
    }

    #[Route('/categories', name: 'app_categorie')]
    public function index(): Response
    {
        $categories = $this->categorieService->getAllCategories();
        return $this->render('categorie/liste_categorie.html.twig', ['categories' => $categories]);
    }

    #[Route('admin/nouvelle/categorie', name: 'nouvelle_categorie', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $file = $request->files->get('couverture_categorie');

            $this->categorieService->createCategorie($nom, $file);

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/nouvelle_categorie.html.twig');
    }

    #[Route('categorie/{id}/modification', name: 'modification_categorie', methods: ['GET', 'POST'])]
    public function edit(Categorie $categorie, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $file = $request->files->get('couverture_categorie');

            $this->categorieService->updateCategorie($categorie, $nom, $file);

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/modification.html.twig', ['categorie' => $categorie]);
    }

    #[Route('categorie/{id}/supprimer', name: 'supprimer_categorie', methods: ['GET'])]
    public function delete(Categorie $categorie): Response
    {
        $this->categorieService->deleteCategorie($categorie);
        return $this->redirectToRoute('app_categorie');
    }
}
