<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\SeanceRepository;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
#[ORM\Table(name: 'seance')]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    private ?string $Titre = null;

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(?string $Titre): self
    {
        $this->Titre = $Titre;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    private ?string $Description = null;

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date ne peut pas être antérieure à aujourd'hui"
    )]
    private ?\DateTimeInterface $Date = null;


    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(?\DateTimeInterface $Date): self
    {
        $this->Date = $Date;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'seances')]
    #[ORM\JoinColumn(name: 'idCoach', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: "id coach est obligatoire")]

    private ?Coach $coach = null;

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): self
    {
        $this->coach = $coach;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'seances')]
    #[ORM\JoinColumn(name: 'idAdherent', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: "id adherent est obligatoire")]

    private ?Adherent $adherent = null;

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): self
    {
        $this->adherent = $adherent;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Type est obligatoire")]

    private ?string $Type = null;

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(?string $Type): self
    {
        $this->Type = $Type;
        return $this;
    }

    #[ORM\Column(name: 'LienVideo',type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: "LienVideo est obligatoire")]
    private ?string $LienVideo = null;

    public function getLienVideo(): ?string
    {
        return $this->LienVideo;
    }

    public function setLienVideo(?string $LienVideo): self
    {
        $this->LienVideo = $LienVideo;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Planning::class, inversedBy: 'seances')]
    #[ORM\JoinColumn(name: 'Planning_id', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: "le planning est obligatoire")]
    private ?Planning $planning = null;

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): self
    {
        $this->planning = $planning;
        return $this;
    }

    #[ORM\Column(name:'heureDebut',type: 'time', nullable: true)]
    #[Assert\NotBlank(message: "L'heure de début est obligatoire")]
    private ?\DateTimeInterface $heureDebut = null;

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut($heureDebut): self
    {
        if (is_string($heureDebut)) {
            $heureDebut = \DateTime::createFromFormat('H:i', $heureDebut);
        }
        $this->heureDebut = $heureDebut;
        return $this;
    }

    #[ORM\Column(name:'heureFin',type: 'time', nullable: true)]
    #[Assert\Expression(
        "this.getHeureFin() > this.getHeureDebut()",
        message: "L'heure de fin doit être après l'heure de début"
    )]
    private ?\DateTimeInterface $heureFin = null;

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin($heureFin): self
    {
        if (is_string($heureFin)) {
            $heureFin = \DateTime::createFromFormat('H:i', $heureFin);
        }
        $this->heureFin = $heureFin;
        return $this;
    }

}
