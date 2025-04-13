<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PlanningRepository;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
#[ORM\Table(name: 'planning')]
class Planning
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
    #[Assert\NotBlank(message: "titre est obligatoire")]
    #[Assert\Regex( pattern: "/^[a-zA-Z0-9]+$/", message: "titre contient uniquement lettres et chiffres" )]
    private ?string $titre = null;

    public function getTitre(): ?string
    {

        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank(message: "Le tarif est obligatoire.")]
    #[Assert\Type(type:'numeric', message: "Le tarif doit être un nombre.")]
    #[Assert\Positive(message: "Le tarif doit être un nombre positif.")]
    private ?float $tarif = null;

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(?float $tarif): self
    {
        $this->tarif = $tarif;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'plannings')]
    #[ORM\JoinColumn(name: 'idCoach', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: "Le coach est obligatoire.")]
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

    #[ORM\OneToMany(targetEntity: PaiementPlanning::class, mappedBy: 'planning')]
    private Collection $paiementPlannings;

    /**
     * @return Collection<int, PaiementPlanning>
     */
    public function getPaiementPlannings(): Collection
    {
        if (!$this->paiementPlannings instanceof Collection) {
            $this->paiementPlannings = new ArrayCollection();
        }
        return $this->paiementPlannings;
    }

    public function addPaiementPlanning(PaiementPlanning $paiementPlanning): self
    {
        if (!$this->getPaiementPlannings()->contains($paiementPlanning)) {
            $this->getPaiementPlannings()->add($paiementPlanning);
        }
        return $this;
    }

    public function removePaiementPlanning(PaiementPlanning $paiementPlanning): self
    {
        $this->getPaiementPlannings()->removeElement($paiementPlanning);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'planning')]
    private Collection $seances;

    public function __construct()
    {
        $this->paiementPlannings = new ArrayCollection();
        $this->seances = new ArrayCollection();
    }

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        if (!$this->seances instanceof Collection) {
            $this->seances = new ArrayCollection();
        }
        return $this->seances;
    }

    public function addSeance(Seance $seance): self
    {
        if (!$this->getSeances()->contains($seance)) {
            $this->getSeances()->add($seance);
        }
        return $this;
    }

    public function removeSeance(Seance $seance): self
    {
        $this->getSeances()->removeElement($seance);
        return $this;
    }

}
