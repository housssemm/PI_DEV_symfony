<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\CategorieRepository;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: 'categorie')]
class Categorie
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
    #[Assert\NotBlank(message: "Le nom de la catégorie est obligatoire.",
        groups: ["creation", "Update"])]
    #[Assert\Regex(
        pattern: "/^[a-zA-Zàâäéèêôùç\s]+$/",
        message: "Le nom de la catégorie doit contenir uniquement des lettres et des espaces.",
        groups: ["creation", "Update"]
    )]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image = null;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
    #[Assert\NotBlank(message: "L'image est obligatoire.",groups: ["creation"])]
    #[Assert\File(
        mimeTypes: ["image/png", "image/jpeg", "image/jpg"],
        mimeTypesMessage: "Veuillez choisir une image au format PNG,JPEG ou JPG.",
        groups: ["creation", "Update"]
    )]

    private ?UploadedFile $imageFile = null;
    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function setImageFile(?UploadedFile $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'categorie')]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        if (!$this->produits instanceof Collection) {
            $this->produits = new ArrayCollection();
        }
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->getProduits()->contains($produit)) {
            $this->getProduits()->add($produit);
        }
        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        $this->getProduits()->removeElement($produit);
        return $this;
    }

}
