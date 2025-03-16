<?php

namespace App\Service;

use App\Entity\Livre;
use App\Entity\Recommandation;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class RecommandationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function hasRecommended(Utilisateur $utilisateur, Livre $livre): ?Recommandation
    {
        $recommandationRepo = $this->entityManager->getRepository(Recommandation::class);

        return $recommandationRepo->findByUserAndLivre($utilisateur, $livre);
    }


    public function addRecommandation(Utilisateur $utilisateur, Livre $livre): void
    {
        $recommandation = new Recommandation();
        $recommandation->setUtilisateur($utilisateur);
        $recommandation->setLivre($livre);
        $recommandation->setDateRecommandation(new \DateTime());

        $this->entityManager->persist($recommandation);
        $this->entityManager->flush();
    }

    public function removeRecommandation(Recommandation $recommandation): void
    {
        $this->entityManager->remove($recommandation);
        $this->entityManager->flush();
    }
}
