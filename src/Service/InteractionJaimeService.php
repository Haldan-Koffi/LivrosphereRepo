<?php

namespace App\Service;

use App\Entity\Livre;
use App\Entity\InteractionJaime;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class InteractionJaimeService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function hasLiked(Utilisateur $utilisateur, Livre $livre): ?InteractionJaime
    {
        $interactionRepo = $this->entityManager->getRepository(InteractionJaime::class);

        return $interactionRepo->findByUserAndLivre($utilisateur, $livre);
    }

    public function addLike(Utilisateur $utilisateur, Livre $livre): void
    {
        $like = new InteractionJaime();
        $like->setUtilisateur($utilisateur);
        $like->setLivre($livre);
        $like->setDateJaime(new \DateTime());

        $this->entityManager->persist($like);
        $this->entityManager->flush();
    }

    public function removeLike(InteractionJaime $like): void
    {
        $this->entityManager->remove($like);
        $this->entityManager->flush();
    }
}
