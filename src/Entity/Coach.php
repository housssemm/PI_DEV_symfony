<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\CoachRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
#[ORM\Table(name: 'coach')]
class Coach extends User
{
    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'année d\'expérience ne peut pas être vide')]
    #[Assert\PositiveOrZero(message: 'L\'année d\'expérience doit être positive ou nulle')]
    private ?int $anneeExperience = null;

    public function getAnneeExperience(): ?int
    {
        return $this->anneeExperience;
    }

    public function setAnneeExperience(int $anneeExperience): static
    {
        $this->anneeExperience = $anneeExperience;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $Certificat_valide = null;

    public function isCertificat_valide(): ?bool
    {
        return $this->Certificat_valide;
    }

    public function setCertificat_valide(?bool $Certificat_valide): self
    {
        $this->Certificat_valide = $Certificat_valide;
        return $this;
    }

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La spécialité ne peut pas être vide')]
    private ?string $specialite = null;

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $Note = null;

    public function getNote(): ?int
    {
        return $this->Note;
    }

    public function setNote(?int $Note): self
    {
        $this->Note = $Note;
        return $this;
    }

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le CV ne peut pas être vide')]
    private ?string $cv = null;

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): static
    {
        $this->cv = $cv;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Offrecoach::class, mappedBy: 'coach')]
    private Collection $offrecoachs;

    /**
     * @return Collection<int, Offrecoach>
     */
    public function getOffrecoachs(): Collection
    {
        if (!$this->offrecoachs instanceof Collection) {
            $this->offrecoachs = new ArrayCollection();
        }
        return $this->offrecoachs;
    }

    public function addOffrecoach(Offrecoach $offrecoach): self
    {
        if (!$this->getOffrecoachs()->contains($offrecoach)) {
            $this->getOffrecoachs()->add($offrecoach);
        }
        return $this;
    }

    public function removeOffrecoach(Offrecoach $offrecoach): self
    {
        $this->getOffrecoachs()->removeElement($offrecoach);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'coach')]
    private Collection $plannings;

    /**
     * @return Collection<int, Planning>
     */
    public function getPlannings(): Collection
    {
        if (!$this->plannings instanceof Collection) {
            $this->plannings = new ArrayCollection();
        }
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): self
    {
        if (!$this->getPlannings()->contains($planning)) {
            $this->getPlannings()->add($planning);
        }
        return $this;
    }

    public function removePlanning(Planning $planning): self
    {
        $this->getPlannings()->removeElement($planning);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'coach')]
    private Collection $seances;

    public function __construct()
    {
        $this->offrecoachs = new ArrayCollection();
        $this->plannings = new ArrayCollection();
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
