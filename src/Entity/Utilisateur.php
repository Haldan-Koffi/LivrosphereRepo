<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Column(length: 50)]
    // private ?string $nom = null;

    // #[ORM\Column(length: 50)]
    // private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;


    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(
    min: 12,
    minMessage: "Le mot de passe doit contenir au moins 12 caractères, une majuscule, un chiffre et un caractère spécial."
    )]
    #[Assert\Regex(
    pattern: "/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/",
    message: "Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial."
    )]
    private ?string $mot_de_passe = null;


    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Livre>
     */
    #[ORM\OneToMany(targetEntity: Livre::class, mappedBy: 'utilisateur')]
    private Collection $livres;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'utilisateur')]
    private Collection $commentaires;

    #[ORM\Column(length: 50)]
    private ?string $pseudonyme = null;

    /**
     * @var Collection<int, InteractionJaime>
     */
    #[ORM\OneToMany(targetEntity: InteractionJaime::class, mappedBy: 'utilisateur')]
    private Collection $interactionJaimes;

    /**
     * @var Collection<int, Recommandation>
     */
    #[ORM\OneToMany(targetEntity: Recommandation::class, mappedBy: 'utilisateur')]
    private Collection $recommandations;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\OneToMany(targetEntity: Categorie::class, mappedBy: 'utilisateur')]
    private Collection $categories;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_inscription = null;

    public function __construct()
    {
        date_default_timezone_set('Europe/Paris'); // Forcer le fuseau horaire
        $this->date_inscription = new \DateTime('now', new \DateTimeZone('Europe/Paris'));


        $this->livres = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->interactionJaimes = new ArrayCollection();
        $this->recommandations = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // public function getNom(): ?string
    // {
    //     return $this->nom;
    // }

    // public function setNom(string $nom): static
    // {
    //     $this->nom = $nom;

    //     return $this;
    // }

    // public function getPrenom(): ?string
    // {
    //     return $this->prenom;
    // }

    // public function setPrenom(string $prenom): static
    // {
    //     $this->prenom = $prenom;

    //     return $this;
    // }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->mot_de_passe;
    }

    public function setMotDePasse(string $mot_de_passe): static
    {
        $this->mot_de_passe = $mot_de_passe;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    // Méthodes pour UserInterface

    // public function getUsername(): string
    // {
    //     return $this->email;
    // }

        // Méthode requise par l'interface UserInterface. Elle renvoie l'identifiant de l'utilisateur (ici, l'email)
    public function getUserIdentifier(): string 
    {
        return $this->email; // Retourne l'email de l'utilisateur comme identifiant unique
    }

    public function eraseCredentials(): void
    {
        // Vous pouvez y ajouter des logiques pour effacer des données sensibles si nécessaire
    }

    public function getPassword(): string
    {
        return $this->mot_de_passe;
    }

    /**
     * @return Collection<int, Livre>
     */
    public function getLivres(): Collection
    {
        return $this->livres;
    }

    public function addLivre(Livre $livre): static
    {
        if (!$this->livres->contains($livre)) {
            $this->livres->add($livre);
            $livre->setUtilisateur($this);
        }

        return $this;
    }

    public function removeLivre(Livre $livre): static
    {
        if ($this->livres->removeElement($livre)) {
            // set the owning side to null (unless already changed)
            if ($livre->getUtilisateur() === $this) {
                $livre->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUtilisateur() === $this) {
                $commentaire->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getPseudonyme(): ?string
    {
        return $this->pseudonyme;
    }

    public function setPseudonyme(string $pseudonyme): static
    {
        $this->pseudonyme = $pseudonyme;

        return $this;
    }

    /**
     * @return Collection<int, InteractionJaime>
     */
    public function getInteractionJaimes(): Collection
    {
        return $this->interactionJaimes;
    }

    public function addInteractionJaime(InteractionJaime $interactionJaime): static
    {
        if (!$this->interactionJaimes->contains($interactionJaime)) {
            $this->interactionJaimes->add($interactionJaime);
            $interactionJaime->setUtilisateur($this);
        }

        return $this;
    }

    public function removeInteractionJaime(InteractionJaime $interactionJaime): static
    {
        if ($this->interactionJaimes->removeElement($interactionJaime)) {
            // set the owning side to null (unless already changed)
            if ($interactionJaime->getUtilisateur() === $this) {
                $interactionJaime->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recommandation>
     */
    public function getRecommandations(): Collection
    {
        return $this->recommandations;
    }

    public function addRecommandation(Recommandation $recommandation): static
    {
        if (!$this->recommandations->contains($recommandation)) {
            $this->recommandations->add($recommandation);
            $recommandation->setUtilisateur($this);
        }

        return $this;
    }

    public function removeRecommandation(Recommandation $recommandation): static
    {
        if ($this->recommandations->removeElement($recommandation)) {
            // set the owning side to null (unless already changed)
            if ($recommandation->getUtilisateur() === $this) {
                $recommandation->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categorie $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCategory(Categorie $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getUtilisateur() === $this) {
                $category->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getDateInscription(): ?DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTimeInterface $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }
}
