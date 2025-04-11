<?php
//
//namespace App\Entity;
//
//use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
//use Doctrine\Common\Collections\Collection;
//use App\Repository\UserRepository;
//use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
//use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Validator\Constraints as Assert;
//
//#[ORM\Entity(repositoryClass: UserRepository::class)]
//#[ORM\InheritanceType("JOINED")]
//#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
//#[ORM\DiscriminatorMap([
//    "user" => "User",
//    "adherent" => "Adherent",
//    "coach" => "Coach",
//    "investisseur_produit" => "InvestisseurProduit",
//    "createur_evenement" => "CreateurEvenement",
//    "admin" => "Admin"  // Ajouter cette ligne pour le rôle admin
//])]
//class User implements UserInterface, PasswordAuthenticatedUserInterface
//{
//    #[ORM\Id]
//    #[ORM\GeneratedValue]
//    #[ORM\Column]
//    private ?int $id = null;
//
//    #[ORM\Column(length: 255, unique: true, nullable: true)]
//    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
//    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
//    private ?string $email = null;
//
//    #[ORM\Column(name: 'MDP', nullable: true)]
//    private ?string $password = null;
//
//    private ?string $plainPassword = null;
//
//    #[ORM\Column(length: 100, nullable: true)]
//    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
//    private ?string $nom = null;
//
//    #[ORM\Column(length: 100, nullable: true)]
//    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide')]
//    private ?string $prenom = null;
//
//    #[ORM\Column(length: 255, nullable: true)]
//    private ?string $image = null;
//
//    // Propriété non mappée pour les rôles (gérée par Symfony Security)
//
//
//    // Relations valides (à conserver si ces entités existent et sont pertinentes)
//    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'user')]
//    private Collection $messages;
//
//    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'user')]
//    private Collection $paniers;
//
//    #[ORM\OneToMany(targetEntity: Participantevenement::class, mappedBy: 'user')]
//    private Collection $participantevenements;
//
//    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
//    private Collection $reclamations;
//
//    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'users')]
//    #[ORM\JoinTable(
//        name: 'participation',
//        joinColumns: [new ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')],
//        inverseJoinColumns: [new ORM\JoinColumn(name: 'evenementId', referencedColumnName: 'id')]
//    )]
//    private Collection $evenements;
//
//    public function __construct()
//    {
//        $this->messages = new ArrayCollection();
//        $this->paniers = new ArrayCollection();
//        $this->participantevenements = new ArrayCollection();
//        $this->reclamations = new ArrayCollection();
//        $this->evenements = new ArrayCollection();
//    }
//
//    public function getId(): ?int
//    {
//        return $this->id;
//    }
//
//    public function getEmail(): ?string
//    {
//        return $this->email;
//    }
//
//    public function setEmail(?string $email): static
//    {
//        $this->email = $email;
//        return $this;
//    }
//    public function getRoles(): array
//    {
//        // Return only the default role for every user
//        return ['ROLE_USER'];
//    }
//
//    public function setRoles(array $roles): self
//    {
//        // Do nothing since roles are fixed.
//        return $this;
//    }
//    public function getUserIdentifier(): string
//    {
//        return (string) $this->email;
//    }
//
//
//    public function getPassword(): ?string
//    {
//        return $this->password;
//    }
//
//    public function setPassword(?string $password): static
//    {
//        $this->password = $password;
//        return $this;
//    }
//
//    public function getPlainPassword(): ?string
//    {
//        return $this->plainPassword;
//    }
//
//    public function setPlainPassword(?string $plainPassword): self
//    {
//        $this->plainPassword = $plainPassword;
//        return $this;
//    }
//
//    public function eraseCredentials(): void
//    {
//        $this->plainPassword = null;
//    }
//
//    public function getNom(): ?string
//    {
//        return $this->nom;
//    }
//
//    public function setNom(?string $nom): static
//    {
//        $this->nom = $nom;
//        return $this;
//    }
//
//    public function getPrenom(): ?string
//    {
//        return $this->prenom;
//    }
//
//    public function setPrenom(?string $prenom): static
//    {
//        $this->prenom = $prenom;
//        return $this;
//    }
//
//    public function getImage(): ?string
//    {
//        return $this->image;
//    }
//
//    public function setImage(?string $image): static
//    {
//        $this->image = $image;
//        return $this;
//    }
//
//    // Méthode pour récupérer le type d’utilisateur basé sur l’héritage
//    public function getDiscriminator(): string
//    {
//        if ($this instanceof Adherent) {
//            return 'adherent';
//        } elseif ($this instanceof Coach) {
//            return 'coach';
//        } elseif ($this instanceof CreateurEvenement) {
//            return 'createur_evenement';
//        } elseif ($this instanceof InvestisseurProduit) {
//            return 'investisseur_produit';
//        } elseif ($this instanceof Admin) {
//            return 'admin';
//        } else {
//            return 'user';
//        }
//    }
//
//    // Méthodes pour les relations (simplifiées)
//    public function getMessages(): Collection
//    {
//        return $this->messages;
//    }
//
//    public function addMessage(Message $message): self
//    {
//        if (!$this->messages->contains($message)) {
//            $this->messages->add($message);
//            $message->setUser($this);
//        }
//        return $this;
//    }
//
//    public function removeMessage(Message $message): self
//    {
//        $this->messages->removeElement($message);
//        return $this;
//    }
//
//    public function getPaniers(): Collection
//    {
//        return $this->paniers;
//    }
//
//    public function addPanier(Panier $panier): self
//    {
//        if (!$this->paniers->contains($panier)) {
//            $this->paniers->add($panier);
//            $panier->setUser($this);
//        }
//        return $this;
//    }
//
//    public function removePanier(Panier $panier): self
//    {
//        $this->paniers->removeElement($panier);
//        return $this;
//    }
//
//    public function getParticipantevenements(): Collection
//    {
//        return $this->participantevenements;
//    }
//
//    public function addParticipantevenement(Participantevenement $participantevenement): self
//    {
//        if (!$this->participantevenements->contains($participantevenement)) {
//            $this->participantevenements->add($participantevenement);
//            $participantevenement->setUser($this);
//        }
//        return $this;
//    }
//
//    public function removeParticipantevenement(Participantevenement $participantevenement): self
//    {
//        $this->participantevenements->removeElement($participantevenement);
//        return $this;
//    }
//
//    public function getReclamations(): Collection
//    {
//        return $this->reclamations;
//    }
//
//    public function addReclamation(Reclamation $reclamation): self
//    {
//        if (!$this->reclamations->contains($reclamation)) {
//            $this->reclamations->add($reclamation);
//            $reclamation->setUser($this);
//        }
//        return $this;
//    }
//
//    public function removeReclamation(Reclamation $reclamation): self
//    {
//        $this->reclamations->removeElement($reclamation);
//        return $this;
//    }
//
//    public function getEvenements(): Collection
//    {
//        return $this->evenements;
//    }
//
//    public function addEvenement(Evenement $evenement): self
//    {
//        if (!$this->evenements->contains($evenement)) {
//            $this->evenements->add($evenement);
//        }
//        return $this;
//    }
//
//    public function removeEvenement(Evenement $evenement): self
//    {
//        $this->evenements->removeElement($evenement);
//        return $this;
//    }
//}


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
#[ORM\DiscriminatorMap([
    "user" => "User",
    "adherent" => "Adherent",
    "coach" => "Coach",
    "investisseur_produit" => "InvestisseurProduit",
    "createur_evenement" => "CreateurEvenement",
    "admin" => "Admin"
])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column(name: 'MDP')]
    #[Assert\NotBlank(message: 'Le mot de passe ne peut pas être vide', groups: ['registration'])]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide')]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'user')]
    private Collection $messages;

    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'user')]
    private Collection $paniers;

    #[ORM\OneToMany(targetEntity: Participantevenement::class, mappedBy: 'user')]
    private Collection $participantevenements;

    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
    private Collection $reclamations;

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'users')]
    #[ORM\JoinTable(
        name: 'participation',
        joinColumns: [new ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'evenementId', referencedColumnName: 'id')]
    )]
    private Collection $evenements;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->paniers = new ArrayCollection();
        $this->participantevenements = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
        $this->evenements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        // Ajouter des rôles en fonction du type d'utilisateur
        if ($this instanceof Admin) {
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this instanceof Coach) {
            $roles[] = 'ROLE_COACH';
        } elseif ($this instanceof Adherent) {
            $roles[] = 'ROLE_ADHERENT';
        } elseif ($this instanceof InvestisseurProduit) {
            $roles[] = 'ROLE_INVESTISSEUR';
        } elseif ($this instanceof CreateurEvenement) {
            $roles[] = 'ROLE_CREATEUR';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        // Pas de stockage direct des rôles dans la base pour l'instant
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getDiscriminator(): string
    {
        if ($this instanceof Adherent) {
            return 'adherent';
        } elseif ($this instanceof Coach) {
            return 'coach';
        } elseif ($this instanceof CreateurEvenement) {
            return 'createur_evenement';
        } elseif ($this instanceof InvestisseurProduit) {
            return 'investisseur_produit';
        } elseif ($this instanceof Admin) {
            return 'admin';
        }
        return 'user';
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        $this->messages->removeElement($message);
        return $this;
    }

    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(Panier $panier): self
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->setUser($this);
        }
        return $this;
    }

    public function removePanier(Panier $panier): self
    {
        $this->paniers->removeElement($panier);
        return $this;
    }

    public function getParticipantevenements(): Collection
    {
        return $this->participantevenements;
    }

    public function addParticipantevenement(Participantevenement $participantevenement): self
    {
        if (!$this->participantevenements->contains($participantevenement)) {
            $this->participantevenements->add($participantevenement);
            $participantevenement->setUser($this);
        }
        return $this;
    }

    public function removeParticipantevenement(Participantevenement $participantevenement): self
    {
        $this->participantevenements->removeElement($participantevenement);
        return $this;
    }

    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setUser($this);
        }
        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        $this->reclamations->removeElement($reclamation);
        return $this;
    }

    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        $this->evenements->removeElement($evenement);
        return $this;
    }
}