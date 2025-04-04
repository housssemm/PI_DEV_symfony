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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
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

    #[ORM\OneToMany(targetEntity: Adherent::class, mappedBy: 'user')]
    private Collection $adherents;

    public function getAdherents(): Collection
    {
        if (!$this->adherents instanceof Collection) {
            $this->adherents = new ArrayCollection();
        }
        return $this->adherents;
    }

    public function addAdherent(Adherent $adherent): self
    {
        if (!$this->getAdherents()->contains($adherent)) {
            $this->getAdherents()->add($adherent);
        }
        return $this;
    }

    public function removeAdherent(Adherent $adherent): self
    {
        $this->getAdherents()->removeElement($adherent);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Admin::class, mappedBy: 'user')]
    private Collection $admins;

    public function getAdmins(): Collection
    {
        if (!$this->admins instanceof Collection) {
            $this->admins = new ArrayCollection();
        }
        return $this->admins;
    }

    public function addAdmin(Admin $admin): self
    {
        if (!$this->getAdmins()->contains($admin)) {
            $this->getAdmins()->add($admin);
        }
        return $this;
    }

    public function removeAdmin(Admin $admin): self
    {
        $this->getAdmins()->removeElement($admin);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Coach::class, mappedBy: 'user')]
    private Collection $coachs;

    public function getCoachs(): Collection
    {
        if (!$this->coachs instanceof Collection) {
            $this->coachs = new ArrayCollection();
        }
        return $this->coachs;
    }

    public function addCoach(Coach $coach): self
    {
        if (!$this->getCoachs()->contains($coach)) {
            $this->getCoachs()->add($coach);
        }
        return $this;
    }

    public function removeCoach(Coach $coach): self
    {
        $this->getCoachs()->removeElement($coach);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: CreateurEvenement::class, mappedBy: 'user')]
    private Collection $createurevenements;

    public function getCreateurevenements(): Collection
    {
        if (!$this->createurevenements instanceof Collection) {
            $this->createurevenements = new ArrayCollection();
        }
        return $this->createurevenements;
    }

    public function addCreateurevenement(CreateurEvenement $createurevenement): self
    {
        if (!$this->getCreateurevenements()->contains($createurevenement)) {
            $this->getCreateurevenements()->add($createurevenement);
        }
        return $this;
    }

    public function removeCreateurevenement(CreateurEvenement $createurevenement): self
    {
        $this->getCreateurevenements()->removeElement($createurevenement);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: InvestisseurProduit::class, mappedBy: 'user')]
    private Collection $investisseurproduits;

    public function getInvestisseurproduits(): Collection
    {
        if (!$this->investisseurproduits instanceof Collection) {
            $this->investisseurproduits = new ArrayCollection();
        }
        return $this->investisseurproduits;
    }

    public function addInvestisseurproduit(InvestisseurProduit $investisseurproduit): self
    {
        if (!$this->getInvestisseurproduits()->contains($investisseurproduit)) {
            $this->getInvestisseurproduits()->add($investisseurproduit);
        }
        return $this;
    }

    public function removeInvestisseurproduit(InvestisseurProduit $investisseurproduit): self
    {
        $this->getInvestisseurproduits()->removeElement($investisseurproduit);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'user')]
    private Collection $messages;

    public function getMessages(): Collection
    {
        if (!$this->messages instanceof Collection) {
            $this->messages = new ArrayCollection();
        }
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->getMessages()->contains($message)) {
            $this->getMessages()->add($message);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        $this->getMessages()->removeElement($message);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'user')]
    private Collection $paniers;

    public function getPaniers(): Collection
    {
        if (!$this->paniers instanceof Collection) {
            $this->paniers = new ArrayCollection();
        }
        return $this->paniers;
    }

    public function addPanier(Panier $panier): self
    {
        if (!$this->getPaniers()->contains($panier)) {
            $this->getPaniers()->add($panier);
        }
        return $this;
    }

    public function removePanier(Panier $panier): self
    {
        $this->getPaniers()->removeElement($panier);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Participantevenement::class, mappedBy: 'user')]
    private Collection $participantevenements;

    public function getParticipantevenements(): Collection
    {
        if (!$this->participantevenements instanceof Collection) {
            $this->participantevenements = new ArrayCollection();
        }
        return $this->participantevenements;
    }

    public function addParticipantevenement(Participantevenement $participantevenement): self
    {
        if (!$this->getParticipantevenements()->contains($participantevenement)) {
            $this->getParticipantevenements()->add($participantevenement);
        }
        return $this;
    }

    public function removeParticipantevenement(Participantevenement $participantevenement): self
    {
        $this->getParticipantevenements()->removeElement($participantevenement);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
    private Collection $reclamations;

    public function getReclamations(): Collection
    {
        if (!$this->reclamations instanceof Collection) {
            $this->reclamations = new ArrayCollection();
        }
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->getReclamations()->contains($reclamation)) {
            $this->getReclamations()->add($reclamation);
        }
        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        $this->getReclamations()->removeElement($reclamation);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'users')]
    #[ORM\JoinTable(
        name: 'participation',
        joinColumns: [
            new ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'evenementId', referencedColumnName: 'id')
        ]
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

    public function getEvenements(): Collection
    {
        if (!$this->evenements instanceof Collection) {
            $this->evenements = new ArrayCollection();
        }
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->getEvenements()->contains($evenement)) {
            $this->getEvenements()->add($evenement);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        $this->getEvenements()->removeElement($evenement);
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
}
