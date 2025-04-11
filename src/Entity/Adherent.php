ADHERENT : 
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\AdherentRepository;

#[ORM\Entity(repositoryClass: AdherentRepository::class)]
#[ORM\Table(name: 'adherent')]
class Adherent extends User
{
    // Poids Property (Weight)
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le poids ne peut pas être vide')]
    #[Assert\Positive(message: 'Le poids doit être positif')]
    private ?float $poids = null;

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        $this->poids = $poids;
        return $this;
    }

    // Taille Property (Height)
    #[ORM\Column]
    #[Assert\NotBlank(message: 'La taille ne peut pas être vide')]
    #[Assert\Positive(message: 'La taille doit être positive')]
    private ?float $taille = null;

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function setTaille(float $taille): static
    {
        $this->taille = $taille;
        return $this;
    }

    // Age Property
    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'âge ne peut pas être vide')]
    #[Assert\Positive(message: 'L\'âge doit être positif')]
    private ?int $age = null;

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;
        return $this;
    }




    public function getObjectifPersonnel(): ?string
    {
        return $this->objectifPersonnel;
    }

    public function setObjectifPersonnel(string $objectifPersonnel): static
    {
        $this->objectifPersonnel = $objectifPersonnel;
        return $this;
    }



    public function getNiveauActivite(): ?string
    {
        return $this->niveauActivite;
    }

    public function setNiveauActivite(string $niveauActivite): static
    {
        $this->niveauActivite = $niveauActivite;
        return $this;
    }
    #[ORM\Column(name: 'Objectif_personnelle', length: 255)]
    #[Assert\NotBlank(message: 'L\'objectif personnel ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['Perdre_Poids', 'Avoir_des_muscles', 'Relaxation'],
        message: 'Choisissez un objectif personnel valide'
    )]
    private ?string $objectifPersonnel = null;

    #[ORM\Column(name: 'Niveau_activites', length: 255)]
    #[Assert\NotBlank(message: 'Le niveau d\'activité ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['Debutant', 'Intermédiaire', 'Avancé'],
        message: 'Choisissez un niveau d\'activités valide'
    )]
    private ?string $niveauActivite = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le genre ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['homme', 'femme'],
        message: 'Choisissez un genre valide'
    )]
    private ?string $genre = null;

    // Relationship with PaiementPlanning (Payments)
    #[ORM\OneToMany(targetEntity: PaiementPlanning::class, mappedBy: 'adherent')]
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

    // Relationship with Seance (Sessions)
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'adherent')]
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

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;
        return $this;
    }

    public function getDescriptionAdherent(): ?string
    {
        return $this->descriptionAdherent;
    }

    public function setDescriptionAdherent(string $descriptionAdherent): static
    {
        $this->descriptionAdherent = $descriptionAdherent;
        return $this;
    }

    public function getAdresseAdherent(): ?string
    {
        return $this->adresseAdherent;
    }

    public function setAdresseAdherent(string $adresseAdherent): static
    {
        $this->adresseAdherent = $adresseAdherent;
        return $this;
    }

    public function getTelephoneAdherent(): ?string
    {
        return $this->telephoneAdherent;
    }

    public function setTelephoneAdherent(string $telephoneAdherent): static
    {
        $this->telephoneAdherent = $telephoneAdherent;
        return $this;
    }
}
