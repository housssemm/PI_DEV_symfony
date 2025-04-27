<?php

namespace App\Form;

use App\Entity\Coach;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CoachRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('anneeExperience', IntegerType::class, [
                'label' => 'Années d\'expérience',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control'
                ]
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'required' => true,
                'choices' => [
                    'Musculation' => 'musculation',
                    'Yoga' => 'yoga',
                    'Gymnastique' => 'gymnastique',
                    'Pilates' => 'pilates',
                    'Danse' => 'danse'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('cv', FileType::class, [
                'label' => 'CV (PDF)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                    ])
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
            'data_class' => Coach::class,
        ]);
    }
} 