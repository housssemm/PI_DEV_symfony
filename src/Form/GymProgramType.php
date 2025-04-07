<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GymProgramType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                ],
                'label' => 'Sexe',
            ])
            ->add('age', IntegerType::class, ['label' => 'Âge'])
            ->add('height', IntegerType::class, ['label' => 'Taille (cm)'])
            ->add('weight', IntegerType::class, ['label' => 'Poids (kg)'])
            ->add('goal', ChoiceType::class, [
                'choices' => [
                    'Perte de poids' => 'perte de poids',
                    'Prise de masse musculaire' => 'prise de masse musculaire',
                    'Remise en forme générale' => 'remise en forme générale',
                ],
                'label' => 'Objectif',
            ])
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Débutant' => 'débutant',
                    'Intermédiaire' => 'intermédiaire',
                    'Avancé' => 'avancé',
                ],
                'label' => 'Niveau',
            ])
            ->add('daysPerWeek', ChoiceType::class, [
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                    '6' => 6,
                    '7' => 7,
                ],
                'label' => 'Jours par semaine',
            ])
            ->add('equipment', TextType::class, [
                'label' => 'Équipement disponible (ex: haltères, banc, poids du corps)',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Générer le programme'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}