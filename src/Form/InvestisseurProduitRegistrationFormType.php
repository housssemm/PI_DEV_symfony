<?php

namespace App\Form;

use App\Entity\InvestisseurProduit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvestisseurProduitRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEntreprise', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom de l\'entreprise'
            ])
            ->add('descriptionInvestisseur', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Description'
            ])
            ->add('adresseInvestisseur', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Adresse'
            ])
            ->add('telephoneInvestisseur', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Téléphone (8 chiffres)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvestisseurProduit::class,
        ]);
    }
} 