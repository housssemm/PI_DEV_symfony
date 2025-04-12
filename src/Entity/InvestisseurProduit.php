<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\InvestisseurProduitRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvestisseurProduitRepository::class)]
#[ORM\Table(name: 'investisseurproduit')]
class InvestisseurProduit extends User
{

    #[ORM\Column(name: 'NomE', length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise ne peut pas être vide')]
    private ?string $nomEntreprise = null;

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;
        return $this;
    }

    #[ORM\Column(name: 'Description', length: 255)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    private ?string $descriptionInvestisseur = null;

    public function getDescriptionInvestisseur(): ?string
    {
        return $this->descriptionInvestisseur;
    }

    public function setDescriptionInvestisseur(string $descriptionInvestisseur): static
    {
        $this->descriptionInvestisseur = $descriptionInvestisseur;
        return $this;
    }

    #[ORM\Column(name: 'Adresse', length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse ne peut pas être vide')]
    private ?string $adresseInvestisseur = null;

    public function getAdresseInvestisseur(): ?string
    {
        return $this->adresseInvestisseur;
    }

    public function setAdresseInvestisseur(string $adresseInvestisseur): static
    {
        $this->adresseInvestisseur = $adresseInvestisseur;
        return $this;
    }

    #[ORM\Column(name: 'Telephone', length: 255)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone ne peut pas être vide')]
    #[Assert\Regex(
        pattern: '/^[0-9]{8}$/',
        message: 'Le numéro de téléphone doit contenir exactement 8 chiffres'
    )]
    private ?string $telephoneInvestisseur = null;

    public function getTelephoneInvestisseur(): ?string
    {
        return $this->telephoneInvestisseur;
    }

    public function setTelephoneInvestisseur(string $telephoneInvestisseur): static
    {
        $this->telephoneInvestisseur = $telephoneInvestisseur;
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
    }}