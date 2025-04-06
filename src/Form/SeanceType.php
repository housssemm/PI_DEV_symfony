<?php

namespace App\Form;

use App\Entity\Seance;
use App\Entity\Coach;
use App\Entity\Adherent;
use App\Entity\Planning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SeanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Titre')
            ->add('Description')
            ->add('Date', null, [
                'widget' => 'single_text',
            ])

            // Dans le FormType, vérifiez les noms des champs
            ->add('Type', ChoiceType::class, [
                'choices' => [
                    'En Direct' => 'EN_DIRECT',
                    'Enregistré' => 'ENREGISTRE'
                ],
                'attr' => [
                    'id' => 'typeSeance', // Pour repérer facilement le <select> en JS
                    'class' => 'form-control'
                ]
            ])
            ->add('LienVideo', UrlType::class, [
                'label' => 'Lien vidéo (obligatoire)',
            ])
            ->add('VideoFile', FileType::class, [
                'label' => 'Fichier vidéo',
                'mapped' => false,
            ])

            ->add('heureDebut', TimeType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
            ])

            ->add('heureFin', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
            ])
            ->add('coach', EntityType::class, [
                'class' => Coach::class,
                'choice_label' => function(Coach $coach) {
                    return $coach->getUser()->getPrenom() . ' ' . $coach->getUser()->getNom();
                },
                'label' => 'Choisir un coach',
                'attr' => ['class' => 'form-select']
            ])
            ->add('adherent', EntityType::class, [
                'class' => Adherent::class,
                'choice_label' => function(Adherent $adherent) {
                    return $adherent->getUser()->getPrenom() . ' ' . $adherent->getUser()->getNom();
                },
                'label' => 'Choisir un adhérent',
                'attr' => ['class' => 'form-select']
            ])
            ->add('planning', EntityType::class, [
                'class' => Planning::class,
                'choice_label' => 'id',
                'label' => 'Choisir un planning',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seance::class,
        ]);
    }
}
