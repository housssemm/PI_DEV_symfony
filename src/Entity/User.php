<?php

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
    "createur_evenement" => "CreateurEvenement"
])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(name: 'MDP')]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide')]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $userType = null;

    #[ORM\OneToMany(targetEntity: Adherent::class, mappedBy: 'user')]
    private Collection $adherents;

    #[ORM\OneToMany(targetEntity: Admin::class, mappedBy: 'user')]
    private Collection $admins;

    #[ORM\OneToMany(targetEntity: Coach::class, mappedBy: 'user')]
    private Collection $coachs;

    #[ORM\OneToMany(targetEntity: CreateurEvenement::class, mappedBy: 'user')]
    private Collection $createurevenements;

    #[ORM\OneToMany(targetEntity: InvestisseurProduit::class, mappedBy: 'user')]
    private Collection $investisseurproduits;

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
        $this->adherents = new ArrayCollection();
        $this->admins = new ArrayCollection();
        $this->coachs = new ArrayCollection();
        $this->createurevenements = new ArrayCollection();
        $this->investisseurproduits = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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
        $this->plainPassword = null; // Efface le mot de passe en clair après utilisation
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

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): static
    {
        $this->userType = $userType;
        return $this;
    }

    // Simplification des méthodes get pour les collections
    public function getAdherents(): Collection
    {
        return $this->adherents;
    }

    public function addAdherent(Adherent $adherent): self
    {
        if (!$this->adherents->contains($adherent)) {
            $this->adherents->add($adherent);
            $adherent->setUser($this); // Si relation bidirectionnelle
        }
        return $this;
    }

    public function removeAdherent(Adherent $adherent): self
    {
        $this->adherents->removeElement($adherent);
        return $this;
    }

    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(Admin $admin): self
    {
        if (!$this->admins->contains($admin)) {
            $this->admins->add($admin);
            $admin->setUser($this);
        }
        return $this;
    }

    public function removeAdmin(Admin $admin): self
    {
        $this->admins->removeElement($admin);
        return $this;
    }

    public function getCoachs(): Collection
    {
        return $this->coachs;
    }

    public function addCoach(Coach $coach): self
    {
        if (!$this->coachs->contains($coach)) {
            $this->coachs->add($coach);
            $coach->setUser($this);
        }
        return $this;
    }

    public function removeCoach(Coach $coach): self
    {
        $this->coachs->removeElement($coach);
        return $this;
    }

    public function getCreateurevenements(): Collection
    {
        return $this->createurevenements;
    }

    public function addCreateurevenement(CreateurEvenement $createurevenement): self
    {
        if (!$this->createurevenements->contains($createurevenement)) {
            $this->createurevenements->add($createurevenement);
            $createurevenement->setUser($this);
        }
        return $this;
    }

    public function removeCreateurevenement(CreateurEvenement $createurevenement): self
    {
        $this->createurevenements->removeElement($createurevenement);
        return $this;
    }

    public function getInvestisseurproduits(): Collection
    {
        return $this->investisseurproduits;
    }

    public function addInvestisseurproduit(InvestisseurProduit $investisseurproduit): self
    {
        if (!$this->investisseurproduits->contains($investisseurproduit)) {
            $this->investisseurproduits->add($investisseurproduit);
            $investisseurproduit->setUser($this);
        }
        return $this;
    }

    public function removeInvestisseurproduit(InvestisseurProduit $investisseurproduit): self
    {
        $this->investisseurproduits->removeElement($investisseurproduit);
        return $this;
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