<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;


use App\Repository\OffrecoachRepository;

#[ORM\Entity(repositoryClass: OffrecoachRepository::class)]
#[ORM\Table(name: 'offrecoach')]
class Offrecoach
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

    #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: 'offrecoachs')]
    #[ORM\JoinColumn(name: 'offre_id', referencedColumnName: 'id', nullable: false)]
    private ?Offre $offre = null;


    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): self
    {
        $this->offre = $offre;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'offrecoachs')]
    #[ORM\JoinColumn(name: 'idCoach', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner un coach.")]
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

    #[ORM\Column(name: 'nouveau_tarif' , type: 'decimal', precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Positive(message: "Le tarif doit être strictement positif.")]
    private ?float $nouveauTarif = null;


    public function getNouveauTarif(): ?float
    {
        return $this->nouveauTarif;
    }

    public function setNouveauTarif(float $nouveauTarif): self
    {
        $this->nouveauTarif = $nouveauTarif;
        return $this;
    }

    #[ORM\Column(name: 'reservation_actuelle' , type: 'integer', nullable: false)]
    private ?int $reservationActuelle = null;

    public function getReservationActuelle(): ?int
    {
        return $this->reservationActuelle;
    }

    public function setReservationActuelle(int $reservationActuelle): self
    {
        $this->reservationActuelle = $reservationActuelle;
        return $this;
    }

    #[ORM\Column(name:'reservation_max' , type: 'integer', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Positive(message: "Les réservations max doivent être strictement positives.")]
    private ?int $reservationMax = null;


    public function getReservationMax(): ?int
    {
        return $this->reservationMax;
    }

    public function setReservationMax(int $reservationMax): self
    {
        $this->reservationMax = $reservationMax;
        return $this;
    }

}
