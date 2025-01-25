<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Entity\Recommandation;
use App\Entity\InteractionJaime;
use App\Repository\LivreRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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

     public function indexapi(Request $request, LivreRepository $livreRepository, EntityManagerInterface $em): Response 
     {
        $livres = $livreRepository->findAll();
        $data = array_map(function ($livre){
            return [
                'id' => $livre->getId(),
                'titre' => $livre->getTitre(),
                'auteur' => $livre->getAuteur(),
                'annee_publication' => $livre->getAnneePublication()->format('Y-m-d'),
                'resume' => $livre->getResume(),
                'couverture' => $livre->getCouverture(),
                'date_ajout' => $livre->getDateAjout()->format('Y-m-d H:i:s'),
                'categorie' => [
                    'id' => $livre->getCategorie()->getId(),
                    'nom' => $livre->getCategorie()->getNom(),
                ],
                'utilisateur' => [
                    'id' => $livre->getUtilisateur()->getId(),
                    'pseudonyme' => $livre->getUtilisateur()->getPseudonyme(),
                ],
            ];
        }, $livres);
        return $this->json($data);
     }


    #[Route('/livre/{id}/info', name: 'livre_information', methods: ['GET', 'POST'])]
    public function show(Livre $livre, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification de l'authentification de l'utilisateur
        $currentUtilisateur = $this->getUser();
        if (!$currentUtilisateur) {
            return $this->redirectToRoute('app_connexion'); // Redirection vers la page de connexion
        }

        // Récupérer les commentaires liés au livre
        $commentaires = $livre->getCommentaires();

        // Vérifier si l'utilisateur a déjà liké ce livre
        $interactionJaimeRepo = $em->getRepository(InteractionJaime::class);
        $utilisateurALike = $interactionJaimeRepo->findByUserAndLivre($currentUtilisateur, $livre);

        // Compter le nombre de likes pour le livre
        $nombreLikes = $interactionJaimeRepo->count(['livre' => $livre]);

        //les recommandations

        $recommandationRepo = $em->getRepository(Recommandation::class);
        $nombreRecommandations = $recommandationRepo->count(['livre' => $livre]);
        $utilisateurARecommande = $recommandationRepo->findByUserAndLivre($currentUtilisateur, $livre);


        // Gérer l'ajout de commentaires
        $commentaire = new Commentaire();
        $commentaire->setLivre($livre);
        $commentaire->setUtilisateur($currentUtilisateur);
        $commentaire->setDateCommentaire(new \DateTime());
        $commentaire->setModificationCommentaire(false);

        // Créer le formulaire de commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commentaire);
            $em->flush();

            // Lier le commentaire au livre
            $livre->addCommentaire($commentaire);

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

            // Redirection pour éviter la double soumission
            return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
        }

        return $this->render('livre/info.html.twig', [
            'livre' => $livre,
            'commentaires' => $commentaires,
            'form' => $form->createView(),
            'utilisateurALike' => $utilisateurALike !== null, // Passer si l'utilisateur a liké ou non
            'nombreLikes' => $nombreLikes, // Passer le nombre de likes
            'nombreRecommandations' => $nombreRecommandations,
            'utilisateurARecommande' => $utilisateurARecommande,
        ]);
    }

    #[Route('livre/nouveau', name: 'nouveau_livre', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CategorieRepository $categorieRepository, Security $security): Response
    {
        // Vérifiez si l'utilisateur est connecté
        $user = $security->getUser();

        // Initialisez l'objet Livre
        $livre = new Livre();

        if ($request->isMethod('POST')) {
            // Récupérez les données du formulaire
            $livre->setTitre($request->request->get('titre'));
            $livre->setAuteur($request->request->get('auteur'));
            $livre->setAnneePublication(new \DateTime($request->request->get('date_publication')));
            $livre->setResume($request->request->get('resume'));

            // Traitez l'image de couverture, si elle est fournie
            if ($request->files->get('couverture')) {
                $file = $request->files->get('couverture');
                $fileName = uniqid() . '.' . $file->guessExtension();

                // Déplacez le fichier dans le répertoire de téléchargement
                $file->move($this->getParameter('upload_directory'), $fileName);

                // Associez le nom du fichier au livre
                $livre->setCouverture($fileName);
            }

            // Associez la catégorie au livre
            $categorieId = $request->request->get('categorie');
            $categorie = $categorieRepository->find($categorieId);
            if ($categorie) {
                $livre->setCategorie($categorie);
            }

            // Associez l'utilisateur connecté au livre
            if ($user) {
                $livre->setUtilisateur($user);
            }

            // Définissez la date d'ajout
            $livre->setDateAjout(new \DateTime());

            // Enregistrez le livre dans la base de données
            $em->persist($livre);
            $em->flush();

            // Redirigez vers une autre page
            return $this->redirectToRoute('app_livre');
        }

        // Récupérez toutes les catégories pour le formulaire
        $categories = $categorieRepository->findAll();

        return $this->render('livre/nouveau_livre.html.twig', [
            'categories' => $categories,
        ]); 
    }


    #[Route('livre/{id}/modification', name: 'modification_livre', methods: ['GET', 'POST'])]
    public function edit(Livre $livre, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
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

    #[Route('livre/{id}/supprimer', name: 'suppression_livre', methods: ['GET'])]
    public function delete(Livre $livre, EntityManagerInterface $em): Response
    {
        $em->remove($livre);
        $em->flush();

        return $this->redirectToRoute('app_livre');
    }

    public function deleteapi(Livre $livre, EntityManagerInterface $em): JsonResponse
    {
        // Supprimer les commentaires associés
        foreach ($livre->getCommentaires() as $commentaire) {
            $em->remove($commentaire);
        }

        // Supprimer les recommandations associées
        foreach ($livre->getRecommandations() as $recommandation) {
            $em->remove($recommandation);
        }

        // Supprimer les interactions j'aimes associées
        foreach ($livre->getInteractionJaimes() as $interactionJaime) {
            $em->remove($interactionJaime);
        }

        // Supprimer le livre
        $em->remove($livre);
        $em->flush();

        return new JsonResponse(['status' => 'Livre et ses commentaires supprimés'], JsonResponse::HTTP_OK);
    }

}
