<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class CommentaireController extends AbstractController
{
    #[Route('admin/commentaires', name: 'app_commentaire', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        $commentaires = $commentaireRepository->findAll();

        return $this->render('commentaire/liste_commentaire.html.twig',[
            'commentaires' => $commentaires]); 
    }

    
    // #[Route('api/commentaires', name: 'app_api_commentaire', methods: ['GET'])]
    public function indexapi(CommentaireRepository $commentaireRepository): Response
    {
        $commentaires = $commentaireRepository->findAll();

        $data = array_map(function ($commentaire) {
            return [
                'id' => $commentaire->getId(),
                'message' => $commentaire->getMessage(),
                'dateCommentaire' => $commentaire->getDateCommentaire()->format('Y-m-d H:i:s'),
                'modificationCommentaire' => $commentaire->isModificationCommentaire(),
                'utilisateur' => $commentaire->getUtilisateur()->getPrenom(),
                'livre' => [
                    'id' => $commentaire->getLivre()->getId(),
                    'titre' => $commentaire->getLivre()->getTitre(),
                    'auteur' => $commentaire->getLivre()->getAuteur(),
                ],
            ];
        }, $commentaires);

        return $this->json($data);
    }

    // #[Route('/api/commentaire/{id}/supprimer', name: 'app_api_commentaire', methods: ['POST'])]
    public function deleteapi(Commentaire $commentaire, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($commentaire);
        $em->flush();

        return new JsonResponse(['status' => 'Commentaire supprimé'], JsonResponse::HTTP_OK);
    }
    

    #[Route('/livre/{id}/commentaire', name: 'ajout_commentaire', methods: ['GET', 'POST'])]
    public function new(Livre $livre, Request $request, EntityManagerInterface $em, CommentaireRepository $commentaireRepository): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setLivre($livre);
        $commentaire->setUtilisateur($this->getUser());
        $commentaire->setDateCommentaire(new \DateTime());
        $commentaire->setModificationCommentaire(false);

        // Récupérer les commentaires associés au livre
        

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commentaire);
            $em->flush();

            // Lier le commentaire au livre
            $livre->addCommentaire($commentaire);

            // Ajouter un message flash
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

            return $this->redirectToRoute('livre_info', ['id' => $livre->getId()]);
        }

        $commentaires = $commentaireRepository->findBy(['livre' => $livre]);

        // Passer les commentaires récupérés à la vue
        return $this->render('commentaire/ajouter.html.twig', [
            'livre' => $livre,
            'commentaires' => $commentaires,  // Ajout de la variable 'commentaires'
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commentaire/{id}/edit', name: 'edit_commentaire', methods: ['GET', 'POST'])]
    public function edit(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        $currentUtilisateur = $this->getUser();
        
        // Vérifier que l'utilisateur connecté est celui qui a créé le commentaire
        if ($commentaire->getUtilisateur() !== $currentUtilisateur) {
            // Si ce n'est pas l'utilisateur qui a créé le commentaire, redirigez-le
            $this->addFlash('error', 'Vous ne pouvez pas modifier ce commentaire.');
            return $this->redirectToRoute('livre_info', ['id' => $commentaire->getLivre()->getId()]);
        }

        // Créer un formulaire pour modifier le commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour la date de modification du commentaire et changer le boolean
            $commentaire->setModificationCommentaire(true);
            
            // Sauvegarder les modifications
            $em->persist($commentaire);
            $em->flush();

            // Ajouter un message flash
            $this->addFlash('success', 'Votre commentaire a été modifié avec succès.');

            // Rediriger l'utilisateur vers la page d'info du livre
            return $this->redirectToRoute('livre_info', ['id' => $commentaire->getLivre()->getId()]);
        }

        // Retourner la vue avec le formulaire
        return $this->render('commentaire/modification.html.twig', [
            'form' => $form->createView(),
            'commentaire' => $commentaire
        ]);
    }

    #[Route('/commentaire/{id}/delete', name: 'delete_commentaire', methods: ['POST'])]
    public function delete(Commentaire $commentaire, EntityManagerInterface $em, Security $security): Response
    {
        $currentUtilisateur = $security->getUser();

        // Vérifier si l'utilisateur a le droit de supprimer le commentaire
        if ($currentUtilisateur === $commentaire->getUtilisateur() || in_array('ROLE_ADMIN', $currentUtilisateur->getRoles())) {
            // Supprimer le commentaire
            $em->remove($commentaire);
            $em->flush();

            // Ajouter un message flash
            $this->addFlash('success', 'Le commentaire a été supprimé avec succès.');
        } else {
            // Ajouter un message flash si l'utilisateur n'a pas les droits
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce commentaire.');
        }

        // Rediriger vers la page du livre
        return $this->redirectToRoute('livre_info', ['id' => $commentaire->getLivre()->getId()]);
    }



}
