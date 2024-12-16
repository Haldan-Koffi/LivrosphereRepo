<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\LivreRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/livre')]
class LivreController extends AbstractController
{
    #[Route('/livres', name: 'app_livre')]
    public function index(Request $request, LivreRepository $livreRepository, EntityManagerInterface $em): Response
    {
        // Récupère la catégorie sélectionnée depuis la requête GET
        $categorieId = $request->query->get('categorie');

        // Récupère toutes les catégories pour le menu déroulant
        $categories = $em->getRepository(Categorie::class)->findAll();

        // Si une catégorie est sélectionnée, on filtre les livres par cette catégorie
        if ($categorieId) {
            $livres = $livreRepository->findBy(['categorie' => $categorieId]);
        } else {
            // Sinon, on récupère tous les livres
            $livres = $livreRepository->findAll();
        }

        return $this->render('livre/liste_livre.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/info', name: 'livre_info', methods: ['GET', 'POST'])]
    public function show(Livre $livre, Request $request, EntityManagerInterface $em): Response
    {

        $currentUtilisateur = $this->getUser();
        if (!$currentUtilisateur) {
            return $this->redirectToRoute('login'); // Redirection vers la page de connexion
        }

        $commentaires = $livre->getCommentaires();

        // Créer un nouveau commentaire
        $commentaire = new Commentaire();
        $commentaire->setLivre($livre);
        $commentaire->setUtilisateur($currentUtilisateur);
        $commentaire->setDateCommentaire(new \DateTime());
        $commentaire->setModificationCommentaire(false);

        // Créer le formulaire de commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder le commentaire dans la base de données
            $em->persist($commentaire);
            $em->flush();

            // Lier le commentaire au livre
            $livre->addCommentaire($commentaire);

            // Ajouter un message flash
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

            // Redirection vers la même page après ajout du commentaire
            return $this->redirectToRoute('livre_info', ['id' => $livre->getId()]);
        }

        return $this->render('livre/info.html.twig', [
            'livre' => $livre,
            'commentaires' => $commentaires,
            'form' => $form->createView(),  // Passer le formulaire à la vue
        ]);
    
    }

    #[Route('/nouveau', name: 'nouveau_livre', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CategorieRepository $categorieRepository, Security $security): Response
    {
        if ($request->isMethod('POST')) {
            $livre = new Livre();
            $livre->setTitre($request->request->get('titre'));
            $livre->setAuteur($request->request->get('auteur'));
            $livre->setAnneePublication(new \DateTime($request->request->get('annee_publication')));
            $livre->setResume($request->request->get('resume'));

            // Récupérer l'utilisateur connecté
            $user = $security->getUser();
            if ($user) {
            // Associer l'utilisateur connecté au livre
                $livre->setUtilisateur($user);
            }


            if ($request->files->get('couverture')) {
            $file = $request->files->get('couverture');
            $fileName = uniqid() . '.' . $file->guessExtension();

            // Déplace le fichier dans le répertoire de téléchargement
            $file->move($this->getParameter('upload_directory'), $fileName);

            // Définit le nom du fichier dans l'entité
            $livre->setCouverture($fileName);
        }

            $categorieId = $request->request->get('categorie');
            $categorie = $categorieRepository->find($categorieId);
            if ($categorie) {
                $livre->setCategorie($categorie);
            }
            
            $livre->setDateAjout(new \DateTime());

            $em->persist($livre);
            $em->flush();

            return $this->redirectToRoute('app_livre');
        }

        $categories = $categorieRepository->findAll();


        return $this->render('livre/nouveau_livre.html.twig', ['categories' => $categories]);
    }

    
    #[Route('/{id}/modification', name: 'modification_livre', methods: ['GET', 'POST'])]
public function edit(Livre $livre, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, Categorie $categorie): Response
{
    // Récupération de toutes les catégories pour le menu déroulant
    $categories = $em->getRepository(Categorie::class)->findAll();

    if ($request->isMethod('POST')) {
        // Mise à jour des informations du livre
        $livre->setTitre($request->request->get('titre'));
        $livre->setAuteur($request->request->get('auteur'));
        $livre->setAnneePublication(new \DateTime($request->request->get('annee_publication')));
        $livre->setResume($request->request->get('resume'));

        // Gestion de la couverture du livre
        $nouvelleCouverture = $request->files->get('couverture');
        if ($nouvelleCouverture) {
            // Gérer l'upload du fichier
            $originalFilename = pathinfo($nouvelleCouverture->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $nouvelleCouverture->guessExtension();

            // Déplacer le fichier dans le dossier 'uploads'
            try {
                $nouvelleCouverture->move(
                    $this->getParameter('uploads_directory'), // Dossier de destination
                    $newFilename
                );
                $livre->setCouverture($newFilename);
            } catch (FileException $e) {
                // Gérer l'erreur de téléchargement si nécessaire
                $this->addFlash('error', 'Erreur lors de l\'upload de la couverture.');
            }
        }

        // Mise à jour de la catégorie sélectionnée
        $categorie = $em->getRepository(Categorie::class)->find($request->request->get('categorie'));
        if ($categorie) {
            $livre->setCategorie($categorie);
        }

        // Sauvegarde des modifications dans la base de données
        $em->flush();

        // Rediriger vers la liste des livres après modification
        return $this->redirectToRoute('app_livre');
    }

    // Rendu du formulaire de modification
    return $this->render('livre/modification.html.twig', [
        'livre' => $livre,
        'categories' => $categories, // Passer la liste des catégories à la vue
    ]);
}



    #[Route('/{id}/supprimer', name: 'supprimer_livre', methods: ['GET'])]
    public function delete(Livre $livre, EntityManagerInterface $em): Response
    {
        $em->remove($livre);
        $em->flush();

        return $this->redirectToRoute('app_livre');
    }
}
