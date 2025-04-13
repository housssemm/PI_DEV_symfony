<?php
//
//namespace App\Entity;
//
//use Doctrine\DBAL\Types\Types;
//use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
//use Doctrine\Common\Collections\Collection;
//
//use App\Repository\EvenementRepository;
//
//#[ORM\Entity(repositoryClass: EvenementRepository::class)]
//#[ORM\Table(name: 'evenement')]
//class Evenement
//{
//    #[ORM\Id]
//    #[ORM\GeneratedValue]
//    #[ORM\Column(type: 'integer')]
//    private ?int $id = null;
//
//    public function getId(): ?int
//    {
//        return $this->id;
//    }
//
//    public function setId(int $id): self
//    {
//        $this->id = $id;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'string', nullable: true)]
//    private ?string $titre = null;
//
//    public function getTitre(): ?string
//    {
//        return $this->titre;
//    }
//
//    public function setTitre(?string $titre): self
//    {
//        $this->titre = $titre;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'text', nullable: true)]
//    private ?string $description = null;
//
//    public function getDescription(): ?string
//    {
//        return $this->description;
//    }
//
//    public function setDescription(?string $description): self
//    {
//        $this->description = $description;
//        return $this;
//    }
//
//
//    #[ORM\Column(name: 'dateDebut', type: 'date', nullable: true)]
//    private ?\DateTimeInterface $dateDebut = null;
//    public function getDateDebut(): ?\DateTimeInterface
//    {
//        return $this->dateDebut;
//    }
//
//    public function setDateDebut(?\DateTimeInterface $dateDebut): self
//    {
//        $this->dateDebut = $dateDebut;
//        return $this;
//    }
//
//    #[ORM\Column(name: 'dateFin', type: 'date', nullable: false)]
//    private ?\DateTimeInterface $dateFin = null;
//
//    public function getDateFin(): ?\DateTimeInterface
//    {
//        return $this->dateFin;
//    }
//
//    public function setDateFin(\DateTimeInterface $dateFin): self
//    {
//        $this->dateFin = $dateFin;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'string', nullable: true)]
//    private ?string $lieu = null;
//
//    public function getLieu(): ?string
//    {
//        return $this->lieu;
//    }
//
//    public function setLieu(?string $lieu): self
//    {
//        $this->lieu = $lieu;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'string', nullable: true)]
//    private ?string $etat = null;
//
//    public function getEtat(): ?string
//    {
//        return $this->etat;
//    }
//
//    public function setEtat(?string $etat): self
//    {
//        $this->etat = $etat;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'float', nullable: false)]
//    private ?float $prix = null;
//
//    public function getPrix(): ?float
//    {
//        return $this->prix;
//    }
//
//    public function setPrix(float $prix): self
//    {
//        $this->prix = $prix;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'blob', nullable: false)]
//    private $image; // Supprimer le typage ?string
//
//    public function getImage(): ?string
//    {
//        // Si l'image est une ressource, récupère-la sous forme de chaîne binaire
//        if (is_resource($this->image)) {
//            return stream_get_contents($this->image); // Retourne une chaîne binaire
//        }
//
//        // Sinon, retourne ce qui est stocké
//        return $this->image;
//    }
//
//    public function setImage(string $image): self
//    {
//        $this->image = $image;
//        return $this;
//    }
//    #[ORM\Column(type: 'string', nullable: false)]
//    private ?string $type = null;
//
//    public function getType(): ?string
//    {
//        return $this->type;
//    }
//
//    public function setType(string $type): self
//    {
//        $this->type = $type;
//        return $this;
//    }
//
//    #[ORM\Column(type: 'string', nullable: false)]
//    private ?string $organisateur = null;
//
//    public function getOrganisateur(): ?string
//    {
//        return $this->organisateur;
//    }
//
//    public function setOrganisateur(string $organisateur): self
//    {
//        $this->organisateur = $organisateur;
//        return $this;
//    }
//
//    #[ORM\Column(name: 'capaciteMaximale', type: 'integer', nullable: false)]
//    private ?int $capaciteMaximale = null;
//
//    public function getCapaciteMaximale(): ?int
//    {
//        return $this->capaciteMaximale;
//    }
//
//    public function setCapaciteMaximale(int $capaciteMaximale): self
//    {
//        $this->capaciteMaximale = $capaciteMaximale;
//        return $this;
//    }
//
//    #[ORM\Column(name: 'idCreateurEvenement', type: 'integer', nullable: true)]
//    private ?int $idCreateurEvenement = null;
//
//    public function getIdCreateurEvenement(): ?int
//    {
//        return $this->idCreateurEvenement;
//    }
//
//    public function setIdCreateurEvenement(?int $idCreateurEvenement): self
//    {
//        $this->idCreateurEvenement = $idCreateurEvenement;
//        return $this;
//    }
//
//    #[ORM\OneToMany(targetEntity: Participantevenement::class, mappedBy: 'evenement')]
//    private Collection $participantevenements;
//
//    public function __construct()
//    {
//        $this->participantevenements = new ArrayCollection();
//    }
//
//    /**
//     * @return Collection<int, Participantevenement>
//     */
//    public function getParticipantevenements(): Collection
//    {
//        if (!$this->participantevenements instanceof Collection) {
//            $this->participantevenements = new ArrayCollection();
//        }
//        return $this->participantevenements;
//    }
//
//    public function addParticipantevenement(Participantevenement $participantevenement): self
//    {
//        if (!$this->getParticipantevenements()->contains($participantevenement)) {
//            $this->getParticipantevenements()->add($participantevenement);
//        }
//        return $this;
//    }
//
//    public function removeParticipantevenement(Participantevenement $participantevenement): self
//    {
//        $this->getParticipantevenements()->removeElement($participantevenement);
//        return $this;
//    }
//
//    //    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'evenements')]
//    //    #[ORM\JoinTable(
//    //        name: 'participation',
//    //        joinColumns: [
//    //            new ORM\JoinColumn(name: 'evenementId', referencedColumnName: 'id')
//    //        ],
//    //        inverseJoinColumns: [
//    //            new ORM\JoinColumn(name: 'userId', referencedColumnName: 'id')
//    //        ]
//    //    )]
//    //    private Collection $users;
//
//    /**
//     * @return Collection<int, User>
//     */
//    public function getUsers(): Collection
//    {
//        if (!$this->users instanceof Collection) {
//            $this->users = new ArrayCollection();
//        }
//        return $this->users;
//    }
//
//    public function addUser(User $user): self
//    {
//        if (!$this->getUsers()->contains($user)) {
//            $this->getUsers()->add($user);
//        }
//        return $this;
//    }
//
//    public function removeUser(User $user): self
//    {
//        $this->getUsers()->removeElement($user);
//        return $this;
//    }
//
//    // Virtual property for base64 image
//    private ?string $base64Image = null;
//
//    public function getBase64Image(): ?string
//    {
//        return $this->base64Image;
//    }
//
//    public function setBase64Image(?string $base64Image): self
//    {
//        $this->base64Image = $base64Image;
//        return $this;
//    }
//}


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le titre doit comporter au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotNull(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit comporter au moins {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(name: 'dateDebut', type: 'date', nullable: true)]
    #[Assert\NotNull(message: "La date de Debut est obligatoire")]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Assert\GreaterThanOrEqual('today', message: "La date de début doit être aujourd'hui ou plus tard")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'dateFin', type: 'date', nullable: true)]
    #[Assert\NotNull(message: "La date de fin est obligatoire")]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull(message: "Le lieu est obligatoire")]
    #[Assert\Length(max: 255, maxMessage: "Le lieu ne peut pas dépasser {{ limit }} caractères")]
    private ?string $lieu = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotNull(message: "L'état est obligatoire")]
    #[Assert\Choice(choices: ['ACTIF', 'EXPIRE', ], message: "L'état doit être : 'À venir', 'En cours' ou 'Terminé'")]
    private ?string $etat = null;

    #[ORM\Column(type: 'float', nullable: false)]
    #[Assert\NotNull(message: "Le prix est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    private ?float $prix = null;

    #[ORM\Column(type: 'blob', nullable: false)]
    #[Assert\NotNull(message: "L'image est obligatoire")]
    private $image;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le type  comporter au moins {{ limit }} caractères",
        maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $type = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le nom de l'organisateur est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "l'organisateur  comporter au moins {{ limit }} caractères",
        maxMessage: "l'organisateur  ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $organisateur = null;

    #[ORM\Column(name: 'capaciteMaximale', type: 'integer', nullable: false)]
    #[Assert\NotNull(message: "La capacité maximale est obligatoire")]
    #[Assert\Positive(message: "La capacité maximale doit être un nombre positif")]
    private ?int $capaciteMaximale = null;

    #[ORM\Column(name: 'idCreateurEvenement', type: 'integer', nullable: true)]
    private ?int $idCreateurEvenement = null;

    #[ORM\OneToMany(targetEntity: Participantevenement::class, mappedBy: 'evenement')]
    private Collection $participantevenements;

    private ?string $base64Image = null;

    public function __construct()
    {
        $this->participantevenements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

//    public function setDateFin(\DateTimeInterface $dateFin): self
//    {
//        $this->dateFin = $dateFin;
//        return $this;
//    }
    public function setDateFin($dateFin): self
    {
        if (is_string($dateFin)) {
            $this->dateFin = new \DateTime($dateFin);
        } elseif ($dateFin instanceof \DateTimeInterface) {
            $this->dateFin = $dateFin;
        }
        // Si null, la validation NotNull attrapera l'erreur
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getImage(): ?string
    {
        if (is_resource($this->image)) {
            return stream_get_contents($this->image);
        }
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getOrganisateur(): ?string
    {
        return $this->organisateur;
    }

    public function setOrganisateur(string $organisateur): self
    {
        $this->organisateur = $organisateur;
        return $this;
    }

    public function getCapaciteMaximale(): ?int
    {
        return $this->capaciteMaximale;
    }

    public function setCapaciteMaximale(int $capaciteMaximale): self
    {
        $this->capaciteMaximale = $capaciteMaximale;
        return $this;
    }

    public function getIdCreateurEvenement(): ?int
    {
        return $this->idCreateurEvenement;
    }

    public function setIdCreateurEvenement(?int $idCreateurEvenement): self
    {
        $this->idCreateurEvenement = $idCreateurEvenement;
        return $this;
    }

    /**
     * @return Collection<int, Participantevenement>
     */
    public function getParticipantevenements(): Collection
    {
        return $this->participantevenements;
    }

    public function addParticipantevenement(Participantevenement $participantevenement): self
    {
        if (!$this->participantevenements->contains($participantevenement)) {
            $this->participantevenements->add($participantevenement);
        }
        return $this;
    }

    public function removeParticipantevenement(Participantevenement $participantevenement): self
    {
        $this->participantevenements->removeElement($participantevenement);
        return $this;
    }

    public function getBase64Image(): ?string
    {
        return $this->base64Image;
    }

    public function setBase64Image(?string $base64Image): self
    {
        $this->base64Image = $base64Image;
        return $this;
    }
}
