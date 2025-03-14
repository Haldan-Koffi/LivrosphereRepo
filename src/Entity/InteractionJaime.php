<?php

namespace App\Entity;

use App\Repository\InteractionJaimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InteractionJaimeRepository::class)]
class InteractionJaime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_jaime = null;

    #[ORM\ManyToOne(inversedBy: 'interactionJaimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Livre $livre = null;

    #[ORM\ManyToOne(inversedBy: 'interactionJaimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    public function __construct()
    {
        date_default_timezone_set('Europe/Paris'); // Forcer le fuseau horaire
        $this->date_jaime = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateJaime(): ?\DateTimeInterface
    {
        return $this->date_jaime;
    }

    public function setDateJaime(\DateTimeInterface $date_jaime): static
    {
        $this->date_jaime = $date_jaime;

        return $this;
    }

    public function getLivre(): ?Livre
    {
        return $this->livre;
    }

    public function setLivre(?Livre $livre): static
    {
        $this->livre = $livre;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
