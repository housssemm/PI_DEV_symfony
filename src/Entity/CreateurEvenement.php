<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\CreateurevenementRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CreateurevenementRepository::class)]
#[ORM\Table(name: 'createurevenement')]
class CreateurEvenement extends User
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'organisation ne peut pas être vide')]
    private ?string $nomOrganisation = null;

    #[ORM\Column(name: 'Description', length: 255)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    private ?string $descriptionCreateur = null;

    #[ORM\Column(name: 'Adresse', length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse ne peut pas être vide')]
    private ?string $adresseCreateur = null;

    #[ORM\Column(name: 'Telephone', length: 255)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone ne peut pas être vide')]
    #[Assert\Regex(
        pattern: '/^[0-9]{8}$/',
        message: 'Le numéro de téléphone doit contenir exactement 8 chiffres'
    )]
    private ?string $telephoneCreateur = null;

    public function getNomOrganisation(): ?string
    {
        return $this->nomOrganisation;
    }

    public function setNomOrganisation(string $nomOrganisation): static
    {
        $this->nomOrganisation = $nomOrganisation;
        return $this;
    }

    public function getDescriptionCreateur(): ?string
    {
        return $this->descriptionCreateur;
    }

    public function setDescriptionCreateur(string $descriptionCreateur): static
    {
        $this->descriptionCreateur = $descriptionCreateur;
        return $this;
    }

    public function getAdresseCreateur(): ?string
    {
        return $this->adresseCreateur;
    }

    public function setAdresseCreateur(string $adresseCreateur): static
    {
        $this->adresseCreateur = $adresseCreateur;
        return $this;
    }

    public function getTelephoneCreateur(): ?string
    {
        return $this->telephoneCreateur;
    }

    public function setTelephoneCreateur(string $telephoneCreateur): static
    {
        $this->telephoneCreateur = $telephoneCreateur;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $certificatValide = null;

    public function isCertificatValide(): ?bool
    {
        return $this->certificatValide;
    }

    public function setCertificatValide(?bool $certificatValide): static
    {
        $this->certificatValide = $certificatValide;
        return $this;
    }
}
