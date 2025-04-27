<?php

namespace App\Form;

use App\Entity\InvestisseurProduit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvestisseurProduitType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        
        $builder
            ->add('nomEntreprise', null, [
                'label' => 'Nom de l\'entreprise',
                'required' => true,
            ])
            ->add('descriptionInvestisseur', null, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('adresseInvestisseur', null, [
                'label' => 'Adresse',
                'required' => true,
            ])
            ->add('telephoneInvestisseur', null, [
                'label' => 'Téléphone',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvestisseurProduit::class,
        ]);
    }
} 