<?php
//
//namespace App\Form;
//
//use App\Entity\Evenement;
//use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Component\Validator\Constraints as Assert;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
//use Symfony\Component\Form\Extension\Core\Type\FileType;
//use Symfony\Component\Form\Extension\Core\Type\IntegerType;
//use Symfony\Component\Form\Extension\Core\Type\TextareaType;
//use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Validator\Constraints\File;
//
//class EvenementFormType extends AbstractType
//{
//    public function buildForm(FormBuilderInterface $builder, array $options): void
//    {
//        $builder
//            ->add('titre', TextType::class, [
//                'label' => 'Titre',
//                'attr' => [
//                    'placeholder' => "Entrez le titre de l'événement",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'Le titre ne peut pas être vide'
//                    ]),
//                    new Assert\Length([
//                        'min' => 3,
//                        'max' => 100,
//                        'minMessage' => 'Le titre doit faire au moins {{ limit }} caractères',
//                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
//                    ]),
//                    new Assert\Regex([
//                        'pattern' => '/^[a-zA-ZÀ-ÿ0-9\s\-\'",.!?]+$/',
//                        'message' => 'Le titre contient des caractères non autorisés'
//                    ])
//                ]
//            ])
//            ->add('description', TextareaType::class, [
//                'label' => 'Description',
//                'attr' => [
//                    'rows' => 3,
//                    'placeholder' => "Décrivez votre événement",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'La description ne peut pas être vide'
//                    ]),
//                    new Assert\Length([
//                        'min' => 10,
//                        'max' => 2000,
//                        'minMessage' => 'La description doit faire au moins {{ limit }} caractères',
//                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
//                    ])
//                ]
//            ])
//            ->add('dateDebut', DateType::class, [
//                'label' => 'Date de début',
//                'widget' => 'single_text',
//                'label_attr' => ['class' => 'form-label'],
//                'attr' => ['class' => 'form-select'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'La date de début est requise'
//                    ]),
//                    new Assert\GreaterThanOrEqual([
//                        'value' => 'today',
//                        'message' => 'La date de début doit être aujourd\'hui ou dans le futur'
//                    ])
//                ]
//            ])
//            ->add('dateFin', DateType::class, [
//                'label' => 'Date de fin',
//                'widget' => 'single_text',
//                'label_attr' => ['class' => 'form-label'],
//                'attr' => ['class' => 'form-select'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'La date de fin est requise'
//                    ]),
//                    new Assert\GreaterThan([
//                        'propertyPath' => 'parent.all[dateDebut].data',
//                        'message' => 'La date de fin doit être après la date de début'
//                    ])
//                ]
//            ])
//            ->add('lieu', TextType::class, [
//                'label' => 'Lieu',
//                'attr' => [
//                    'placeholder' => "Entrez le lieu de l'événement",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'Le lieu ne peut pas être vide'
//                    ]),
//                    new Assert\Length([
//                        'min' => 3,
//                        'max' => 150,
//                        'minMessage' => 'Le lieu doit faire au moins {{ limit }} caractères'
//                    ])
//                ]
//            ])
//            ->add('etat', ChoiceType::class, [
//                'label' => 'État',
//                'choices' => [
//                    'Actif' => 'ACTIF',
//                    'Expiré' => 'EXPIRE',
//
//                ],
//                'placeholder' => 'Sélectionnez un état',
//                'label_attr' => ['class' => 'form-label'],
//                'attr' => ['class' => 'form-select'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'Veuillez sélectionner un état'
//                    ])
//                ]
//            ])
//            ->add('prix', IntegerType::class, [
//                'label' => 'Prix',
//                'attr' => [
//                    'placeholder' => "Entrez le prix",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'Le prix est requis'
//                    ]),
//                    new Assert\PositiveOrZero([
//                        'message' => 'Le prix ne peut pas être négatif'
//                    ]),
//                    new Assert\LessThanOrEqual([
//                        'value' => 10000,
//                        'message' => 'Le prix ne peut pas dépasser {{ limit }}'
//                    ])
//                ]
//            ])
//            ->add('capaciteMaximale', IntegerType::class, [
//                'label' => 'Capacité maximale',
//                'attr' => [
//                    'placeholder' => "Entrez la capacité maximale",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'La capacité maximale est requise'
//                    ]),
//                    new Assert\Positive([
//                        'message' => 'La capacité doit être un nombre positif'
//                    ]),
//                    new Assert\LessThanOrEqual([
//                        'value' => 1000,
//                        'message' => 'La capacité ne peut pas dépasser {{ limit }} personnes'
//                    ])
//                ]
//            ])
//            ->add('image', FileType::class, [
//                'label' => 'Image (fichier JPG/PNG)',
//                'mapped' => false,
//                'required' => false,
//                'attr' => ['accept' => 'image/*'],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new File([
//                        'maxSize' => '5M',
//                        'mimeTypes' => [
//                            'image/jpeg',
//                            'image/png',
//                            'image/jpg'
//                        ],
//                        'mimeTypesMessage' => 'Veuillez télécharger une image JPG ou PNG valide.',
//                        'maxSizeMessage' => 'La taille de l\'image ne peut pas dépasser {{ limit }} {{ suffix }}'
//                    ])
//                ]
//            ])
//            ->add('type', TextType::class, [
//                'label' => 'Type',
//                'attr' => [
//                    'placeholder' => "Entrez le type de l'evenement",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'Le type est requis'
//                    ]),
//                    new Assert\Length([
//                        'min' => 3,
//                        'max' => 50,
//                        'minMessage' => 'Le type doit faire au moins {{ limit }} caractères'
//                    ])
//                ]
//            ])
//            ->add('organisateur', TextType::class, [
//                'label' => 'Organisateur',
//                'attr' => [
//                    'placeholder' => "Entrez le nom de l'organisateur",
//                    'class' => "form-control"
//                ],
//                'label_attr' => ['class' => 'form-label'],
//                'constraints' => [
//                    new Assert\NotBlank([
//                        'message' => 'Le nom de l\'organisateur est requis'
//                    ]),
//                    new Assert\Length([
//                        'min' => 3,
//                        'max' => 100,
//                        'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères'
//                    ]),
//                    new Assert\Regex([
//                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-\.]+$/',
//                        'message' => 'Le nom ne doit contenir que des lettres, espaces et tirets'
//                    ])
//                ]
//            ])
//            ->add('submit', SubmitType::class, [
//                'label' => 'Ajouter',
//                'attr' => [
//                    'class' => 'btn btn-primary mt-3'
//                ]
//            ]);
//    }
//
//    public function configureOptions(OptionsResolver $resolver): void
//    {
//        $resolver->setDefaults([
//            'data_class' => Evenement::class,
//            'attr' => ['novalidate' => 'novalidate'] // Disable HTML5 validation
//        ]);
//    }
//}
//
//
//
//
//
//
//


namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EvenementFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
            ])
//            ->add('etat', ChoiceType::class, [
//                'label' => 'État',
//                'choices' => [
//                    'EXPIRE' => 'EXPIRE',
//                    'ACTIF' => 'ACTIF',
//
//                ],
//                'required' => false,
//                'placeholder' => 'Sélectionnez un état'
//            ])
            ->add('prix', IntegerType::class, [
                'label' => 'Prix',
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (Tous formats)',

                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'image/*',  // All image types
                            'image/svg+xml',  // Explicitly allow SVG
                            'application/pdf', // If you want PDF too
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
            ])
            ->add('type', TextType::class, [
                'label' => 'Type d’événement',
                'required' => true,
            ])
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
                'required' => true,
            ])
            ->add('capaciteMaximale', IntegerType::class, [
                'label' => 'Capacité maximale',
                'required' => true,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
