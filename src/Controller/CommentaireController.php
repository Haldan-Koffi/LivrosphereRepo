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
    #[Route('/admin/commentaires', name: 'app_commentaire', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        $commentaires = $commentaireRepository->findAll();

        return $this->render('commentaire/liste_commentaire.html.twig',[
            'commentaires' => $commentaires]); 
    }

    public function indexapi(CommentaireRepository $commentaireRepository): JsonResponse
    {
        $commentaires = $commentaireRepository->findAll();

        $data = array_map(function ($commentaire) {
            return [
                'id' => $commentaire->getId(),
                'message' => $commentaire->getMessage(),
                'dateCommentaire' => $commentaire->getDateCommentaire()->format('Y-m-d'),
                'modificationCommentaire' => $commentaire->isModificationCommentaire(),
                'utilisateur' => $commentaire->getUtilisateur()->getPseudonyme(),
                'livre' => [
                    'id' => $commentaire->getLivre()->getId(),
                    'titre' => $commentaire->getLivre()->getTitre(),
                    'auteur' => $commentaire->getLivre()->getAuteur(),
                ],
            ];
        }, $commentaires);

        return $this->json($data);
    }

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

        

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commentaire);
            $em->flush();

            $livre->addCommentaire($commentaire);

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

            return $this->redirectToRoute('livre_information', ['id' => $livre->getId()]);
        }

        $commentaires = $commentaireRepository->findBy(['livre' => $livre]);

        return $this->render('commentaire/ajouter.html.twig', [
            'livre' => $livre,
            'commentaires' => $commentaires,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commentaire/{id}/edit', name: 'edit_commentaire', methods: ['GET', 'POST'])]
    public function edit(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        $currentUtilisateur = $this->getUser();

        if ($commentaire->getUtilisateur() !== $currentUtilisateur) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier ce commentaire.');
            return $this->redirectToRoute('livre_information', ['id' => $commentaire->getLivre()->getId()]);
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $commentaire->setDateCommentaire(new \DateTime());

            
            $commentaire->setModificationCommentaire(true);

            $em->persist($commentaire);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a été modifié avec succès.');

            return $this->redirectToRoute('livre_information', ['id' => $commentaire->getLivre()->getId()]);
        }

        return $this->render('commentaire/modification.html.twig', [
            'form' => $form->createView(),
            'commentaire' => $commentaire
        ]);
    }


    #[Route('/commentaire/{id}/delete', name: 'delete_commentaire', methods: ['POST'])]
    public function delete(Commentaire $commentaire, EntityManagerInterface $em, Security $security): Response
    {
        $currentUtilisateur = $security->getUser();

        if ($currentUtilisateur === $commentaire->getUtilisateur() || in_array('ROLE_ADMIN', $currentUtilisateur->getRoles())) {
            
            $em->remove($commentaire);
            $em->flush();

            
            $this->addFlash('success', 'Le commentaire a été supprimé avec succès.');
        } else {
            
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce commentaire.');
        }

        return $this->redirectToRoute('livre_information', ['id' => $commentaire->getLivre()->getId()]);
    }

}
