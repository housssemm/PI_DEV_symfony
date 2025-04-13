<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Investisseurproduit;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Entrez une courte description...']
            ])
            ->add('imageFile', FileType::class, [
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'En Stock' => 'Stock',
                    //'En Rupture' => 'Rupture',
                ],
                'label' => 'État du produit',
            ])
            ->add('quantite', IntegerType::class, [
                'empty_data' => 1,
            ])
            ->add('prix', NumberType::class, [
                'empty_data' => 1.0,
            ])
            ->add('investisseurproduit', EntityType::class, [
                'class' => Investisseurproduit::class,
                'choice_label' => function(Investisseurproduit $Investisseurproduit) {
                    return $Investisseurproduit->getUser()->getPrenom() . ' ' . $Investisseurproduit->getUser()->getNom();
                },
                'label' => 'Choisir un Investisseur',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => function(Categorie $categorie) {
                    return $categorie->getNom();
                },
                'label' => 'Choisir une Categorie',
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
