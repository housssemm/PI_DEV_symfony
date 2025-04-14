<?php

namespace App\Form;

use App\Entity\Seance;
use App\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
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
                'label' => 'Fichier vidéo (MP4, MOV, AVI, WEBM)',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '500M',
                        'mimeTypes' => [
                            'video/mp4',
                            'video/quicktime', // MOV
                            'video/x-msvideo', // AVI
                            'video/webm',
                        ],
                    ])
                ],
            ])
            ->add('heureDebut', TimeType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
            ])
            ->add('heureFin', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
            ])
            ->add('adherent', EntityType::class, [
                'class' => Adherent::class,
                'choice_label' => function(Adherent $adherent) {
                    return $adherent->getPrenom() . ' ' . $adherent->getNom();
                },
                'label' => 'Choisir un adhérent',
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
