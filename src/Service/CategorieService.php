<?php

namespace App\Service;

use App\Entity\User; 
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CategorieService
{
    private EntityManagerInterface $em;
    private CategorieRepository $categorieRepository;
    private string $uploadDirectory;

    public function __construct(EntityManagerInterface $em, CategorieRepository $categorieRepository, string $uploadDirectory)
    {
        $this->em = $em;
        $this->categorieRepository = $categorieRepository;
        $this->uploadDirectory = $uploadDirectory;
    }

    public function getAllCategories(): array
    {
        return $this->categorieRepository->findAll();
    }

    public function createCategorie(string $nom, $file = null, $user): Categorie
    {
        $categorie = new Categorie();
        $categorie->setNom($nom);
        $categorie->setDateCreation(new \DateTime());

        $categorie->setUtilisateur($user);

        if ($file) {
            $fileName = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->uploadDirectory, $fileName);
                $categorie->setCouvertureCategorie($fileName);
            } catch (FileException $e) {
                throw new \Exception('Erreur lors du téléchargement du fichier.');
            }
        }

        $this->em->persist($categorie);
        $this->em->flush();

        return $categorie;
    }

    public function updateCategorie(Categorie $categorie, string $nom, $file = null): Categorie
    {
        $categorie->setNom($nom);

        if ($file) {
            $fileName = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->uploadDirectory, $fileName);
                $categorie->setCouvertureCategorie($fileName);
            } catch (FileException $e) {
                throw new \Exception('Erreur lors du téléchargement du fichier.');
            }
        }

        $categorie->setDateCreation(new \DateTime());
        $this->em->flush();

        return $categorie;
    }

    public function deleteCategorie(Categorie $categorie): void
    {
        // Pour chaque livre de la catégorie, supprimer d'abord ses dépendances
        foreach ($categorie->getLivres() as $livre) {
            // Supprimer les commentaires associés au livre
            foreach ($livre->getCommentaires() as $commentaire) {
                $this->em->remove($commentaire);
            }
            // Supprimer les interactions "jaime" associées au livre
            foreach ($livre->getInteractionJaimes() as $interaction) {
                $this->em->remove($interaction);
            }
            // Supprimer les recommandations associées au livre
            foreach ($livre->getRecommandations() as $recommandation) {
                $this->em->remove($recommandation);
            }
            // Supprimer le livre lui-même
            $this->em->remove($livre);
        }

        // Supprimer la catégorie
        $this->em->remove($categorie);
        $this->em->flush();
    }
}
