<?php

namespace App\Form;

use App\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 0.1,
                    'class' => 'form-control'
                ]
            ])
            ->add('taille', NumberType::class, [
                'label' => 'Taille (cm)',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 0.1,
                    'class' => 'form-control'
                ]
            ])
            ->add('age', NumberType::class, [
                'label' => 'Âge',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control'
                ]
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'required' => true,
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('objectifPersonnel', ChoiceType::class, [
                'label' => 'Objectif personnel',
                'required' => true,
                'choices' => [
                    'Perdre du poids' => 'Perdre_Poids',
                    'Avoir des muscles' => 'Avoir_des_muscles',
                    'Relaxation' => 'Relaxation'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('niveauActivite', ChoiceType::class, [
                'label' => 'Niveau d\'activité',
                'required' => true,
                'choices' => [
                    'Débutant' => 'Debutant',
                    'Intermédiaire' => 'Intermediaire',
                    'Avancé' => 'Avance'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }
} 