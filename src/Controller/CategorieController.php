<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->render('categorie/liste_categorie.html.twig', ['categories' => $categories]);
    }

    #[Route('/nouvelle/categorie', name: 'nouvelle_categorie', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $categorie = new Categorie();
            $categorie->setNom($request->request->get('nom'));


            if ($request->files->get('couverture_categorie')) {
            $file = $request->files->get('couverture_categorie');
            $fileName = uniqid() . '.' . $file->guessExtension();

            // Déplace le fichier dans le répertoire de téléchargement
            $file->move($this->getParameter('upload_directory'), $fileName);

            // Définit le nom du fichier dans l'entité
            $categorie->setCouvertureCategorie($fileName);
        }
            $categorie->setDateCreation(new \DateTime());

            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/nouvelle_categorie.html.twig');
    }

    #[Route('categorie/{id}/modification', name: 'modification_categorie', methods: ['GET', 'POST'])]
    public function edit(Categorie $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $categorie->setNom($request->request->get('nom'));
            $categorie->SetCouvertureCategorie($request->request->get('couverture_categorie'));
            $categorie->SetDateCreation($request->request->get('date_creation'));
            
            $em->flush();

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/modification.html.twig', ['categorie' => $categorie]);
    }

    #[Route('categorie/{id}/supprimer', name: 'supprimer_categorie', methods: ['GET'])]
    public function delete(Categorie $categorie, EntityManagerInterface $em): Response
    {
        $em->remove($categorie);
        $em->flush();

        return $this->redirectToRoute('app_categorie');
    }
}
