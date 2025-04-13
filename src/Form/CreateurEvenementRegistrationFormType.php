<?php

namespace App\Form;

use App\Entity\CreateurEvenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateurEvenementRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOrganisation', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom de l\'organisation'
            ])
            ->add('descriptionCreateur', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Description'
            ])
            ->add('adresseCreateur', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Adresse'
            ])
            ->add('telephoneCreateur', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Téléphone (8 chiffres)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateurEvenement::class,
        ]);
    }
} 