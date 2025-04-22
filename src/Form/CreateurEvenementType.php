<?php

namespace App\Form;

use App\Entity\CreateurEvenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateurEvenementType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        
        $builder
            ->add('nomOrganisation', null, [
                'label' => 'Nom de l\'organisation',
                'required' => true,
            ])
            ->add('descriptionCreateur', null, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('adresseCreateur', null, [
                'label' => 'Adresse',
                'required' => true,
            ])
            ->add('telephoneCreateur', null, [
                'label' => 'Téléphone',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateurEvenement::class,
        ]);
    }
} 