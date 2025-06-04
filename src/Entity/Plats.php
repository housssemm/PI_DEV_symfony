<?php

namespace App\Entity;

use App\Repository\PlatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatsRepository::class)]
class Plats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 200)]
    private ?string $ingredients = null;

    #[ORM\Column(length: 500)]
    private ?string $description = null;

    #[ORM\Column(name: "nb_calories")]
    private ?int $nbCalories = null;

    #[ORM\Column(name: "id_nutritionist")]
    private ?int $idNutritionist = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(string $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getNbCalories(): ?int
    {
        return $this->nbCalories;
    }

    public function setNbCalories(int $nbCalories): self
    {
        $this->nbCalories = $nbCalories;
        return $this;
    }

    public function getIdNutritionist(): ?int
    {
        return $this->idNutritionist;
    }

    public function setIdNutritionist(int $idNutritionist): self
    {
        $this->idNutritionist = $idNutritionist;
        return $this;
    }
}




