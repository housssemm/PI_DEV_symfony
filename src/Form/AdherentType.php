<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Enum\ObjectifEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        
        $builder
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => true,
                'scale' => 2,
            ])
            ->add('taille', NumberType::class, [
                'label' => 'Taille (cm)',
                'required' => true,
                'scale' => 2,
            ])
            ->add('age', NumberType::class, [
                'label' => 'Âge',
                'required' => true,
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'required' => true,
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                    'Autre' => 'autre',
                ],
            ])
            ->add('objectifPersonnel', ChoiceType::class, [
                'label' => 'Objectif personnel',
                'required' => true,
                'choices' => ObjectifEnum::cases(),
                'choice_label' => function (ObjectifEnum $objectif) {
                    return match($objectif) {
                        ObjectifEnum::PERDRE_POIDS => 'Perdre du poids',
                        ObjectifEnum::AVOIR_DES_MUSCLES => 'Avoir des muscles',
                        ObjectifEnum::RELAXATION => 'Relaxation',
                    };
                },
            ])
            ->add('niveauActivite', ChoiceType::class, [
                'label' => 'Niveau d\'activité',
                'required' => true,
                'choices' => [
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance',
                ],
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