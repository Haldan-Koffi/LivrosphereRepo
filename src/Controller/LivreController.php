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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class LivreController extends AbstractController
{
    #[Route('/livres', name: 'app_livre')]
    public function index(Request $request, LivreRepository $livreRepository, EntityManagerInterface $em): Response
    {
        $categorieId = $request->query->get('categorie');
        $categories = $em->getRepository(Categorie::class)->findAll();

        if ($categorieId) {
            $livres = $livreRepository->findBy(['categorie' => $categorieId]);
        } else {
            $livres = $livreRepository->findAll();
        }

        return $this->render('livre/liste_livre.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
        ]);
    }

    #[Route('/livres/filtrer', name: 'filtrer_livres', methods: ['GET'])]
    public function filtrerLivres(Request $request, LivreRepository $livreRepository): JsonResponse
    {
        $categorieId = $request->query->get('categorie');

        if ($categorieId) {
            $livres = $livreRepository->findBy(['categorie' => $categorieId]);
        } else {
            $livres = $livreRepository->findAll();
        }

        $data = array_map(function ($livre) {
            return [
                'id' => $livre->getId(),
                'titre' => $livre->getTitre(),
                'auteur' => $livre->getAuteur(),
                'couverture' => $livre->getCouverture(),
                'resume' => $livre->getResume(),
                'annee_publication' => $livre->getAnneePublication() ? $livre->getAnneePublication()->format('Y-m-d') : null,
            ];
        }, $livres);

        return new JsonResponse($data);
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
                'date_ajout' => $livre->getDateAjout()->format('Y-m-d'),
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

    #[Route('/livre/nouveau', name: 'nouveau_livre', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CategorieRepository $categorieRepository, Security $security, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        try {
            $user = $security->getUser();
            if (!$user) {
                throw new AccessDeniedHttpException('Vous devez être connecté pour ajouter un nouveau livre.');
            }

            $csrfToken = $csrfTokenManager->getToken('nouveau_livre')->getValue();

            if ($request->isMethod('POST')) {
                $token = $request->request->get('_csrf_token');
                if (!$csrfTokenManager->isTokenValid(new CsrfToken('nouveau_livre', $token))) {
                    throw new AccessDeniedHttpException('Le token CSRF est invalide.');
                }

                $livre = new Livre();
                $livre->setTitre($request->request->get('titre'));
                $livre->setAuteur($request->request->get('auteur'));
                $livre->setAnneePublication(new \DateTime($request->request->get('annee_publication')));
                $livre->setResume($request->request->get('resume'));

                if ($request->files->get('couverture')) {
                    $file = $request->files->get('couverture');
                    $fileName = uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('upload_directory'), $fileName);
                    $livre->setCouverture($fileName);
                }

                $categorieId = $request->request->get('categorie');
                $categorie = $categorieRepository->find($categorieId);
                if (!$categorie) {
                    throw new NotFoundHttpException('Catégorie non trouvée.');
                }

                $livre->setCategorie($categorie);
                $livre->setUtilisateur($user);
                $livre->setDateAjout(new \DateTime());


                $em->persist($livre);
                $em->flush();

                return $this->redirectToRoute('app_livre');
            }

            $categories = $categorieRepository->findAll();

            return $this->render('livre/nouveau_livre.html.twig', [
                'categories' => $categories,
                'csrf_token' => $csrfToken,
            ]);
        } catch (AccessDeniedHttpException $e) {
            throw new AccessDeniedHttpException('Accès refusé.', $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la création du livre : ' . $e->getMessage());
        }
    } 

    #[Route('/livre/{id}/info', name: 'livre_information', methods: ['GET', 'POST'])]
    public function show(Livre $livre, Request $request, EntityManagerInterface $em): Response
    {
        if (!$livre) {
            throw new NotFoundHttpException("Le livre demandé n'existe pas.");
        }

        $currentUtilisateur = $this->getUser();
        if (!$currentUtilisateur) {
            return $this->redirectToRoute('app_connexion');
        }

        $commentaires = $livre->getCommentaires();
        $interactionJaimeRepo = $em->getRepository(InteractionJaime::class);
        $utilisateurALike = $interactionJaimeRepo->findByUserAndLivre($currentUtilisateur, $livre);
        $nombreLikes = $interactionJaimeRepo->count(['livre' => $livre]);

        $recommandationRepo = $em->getRepository(Recommandation::class);
        $nombreRecommandations = $recommandationRepo->count(['livre' => $livre]);
        $utilisateurARecommande = $recommandationRepo->findByUserAndLivre($currentUtilisateur, $livre);

        $commentaire = new Commentaire();
        $commentaire->setLivre($livre);
        $commentaire->setUtilisateur($currentUtilisateur);
        $commentaire->setDateCommentaire(new \DateTime());
        $commentaire->setModificationCommentaire(false);

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commentaire);
            $em->flush();

            $livre->addCommentaire($commentaire);
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

            return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
        }

        return $this->render('livre/info.html.twig', [
            'livre' => $livre,
            'commentaires' => $commentaires,
            'form' => $form->createView(),
            'utilisateurALike' => $utilisateurALike !== null,
            'nombreLikes' => $nombreLikes,
            'nombreRecommandations' => $nombreRecommandations,
            'utilisateurARecommande' => $utilisateurARecommande,
        ]);
    }

    #[Route('/livre/{id}/supprimer', name: 'suppression_livre', methods: ['GET','POST'])]
    public function delete(Livre $livre = null, EntityManagerInterface $em): Response
    {
        if (!$livre) {
            throw new NotFoundHttpException("Le livre que vous tentez de supprimer n'existe pas.");
        }

        $currentUser = $this->getUser();

        if (
            !$currentUser ||
            (
                !in_array('ROLE_ADMIN', $currentUser->getRoles()) &&
                $livre->getUtilisateur()->getId() !== $currentUser->getId()
            )
        ) {
            throw $this->createAccessDeniedException('Vous n\'avez pas l\'autorisation de supprimer ce livre.');
        }

        foreach ($livre->getCommentaires() as $commentaire) {
            $em->remove($commentaire);
        }

        foreach ($livre->getInteractionJaimes() as $interaction) {
            $em->remove($interaction);
        }

        foreach ($livre->getRecommandations() as $recommandation) {
            $em->remove($recommandation);
        }

        try {
            $em->remove($livre);
            $em->flush();
            $this->addFlash('success', 'Livre supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du livre.');
        }

        return $this->redirectToRoute('app_livre');
    }

    public function deleteapi(Livre $livre = null, EntityManagerInterface $em): JsonResponse
    {
        if (!$livre) {
            throw new NotFoundHttpException("Le livre que vous tentez de supprimer n'existe pas.");
        }

        try {
            foreach ($livre->getCommentaires() as $commentaire) {
                $em->remove($commentaire);
            }

            foreach ($livre->getRecommandations() as $recommandation) {
                $em->remove($recommandation);
            }

            foreach ($livre->getInteractionJaimes() as $interactionJaime) {
                $em->remove($interactionJaime);
            }

            $em->remove($livre);
            $em->flush();

            return new JsonResponse(['status' => 'Livre et ses commentaires supprimés'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression du livre'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/livre/{id}/modification', name: 'modification_livre', methods: ['GET', 'POST'])]
    public function edit(Livre $livre, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, CsrfTokenManagerInterface $csrfTokenManager, CategorieRepository $categorieRepository): Response
    {
        if (!$livre) {
            throw new NotFoundHttpException("Le livre que vous tentez de modifier n'existe pas.");
        }

        $categories = $categorieRepository->findAll();

        // Générer un token CSRF pour la modification de livre
        $csrfToken = $csrfTokenManager->getToken('modification_livre_' . $livre->getId())->getValue();

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('modification_livre_' . $livre->getId(), $token))) {
                throw new AccessDeniedHttpException('Le token CSRF est invalide.');
            }

            $livre->setTitre($request->request->get('titre'));
            $livre->setAuteur($request->request->get('auteur'));
            $livre->setAnneePublication(new \DateTime($request->request->get('annee_publication')));
            $livre->setResume($request->request->get('resume'));

            $nouvelleCouverture = $request->files->get('couverture');
            if ($nouvelleCouverture) {
                try {
                    $newFilename = $slugger->slug(pathinfo($nouvelleCouverture->getClientOriginalName(), PATHINFO_FILENAME))
                        . '-' . uniqid() . '.' . $nouvelleCouverture->guessExtension();
                    $nouvelleCouverture->move($this->getParameter('upload_directory'), $newFilename);
                    $livre->setCouverture($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la couverture.');
                }
            }

            $em->flush();

            return $this->redirectToRoute('app_livre');
        }

        return $this->render('livre/modification.html.twig', [
            'livre' => $livre,
            'csrf_token' => $csrfToken,
            'categories' => $categories,
        ]);
    }
}