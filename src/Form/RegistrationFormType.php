<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\CreateurEvenement;
use App\Entity\InvestisseurProduit;
use App\Enum\ObjectifEnum;
use App\Enum\SpecialiteEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide'])
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2MB',
                    ]),
                ],
            ])
            ->add('userType', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Adhérent' => 'adherent',
                    'Coach' => 'coach',
                    'Investisseur de Produits' => 'investisseur_produit',
                    'Créateur d\'Événements' => 'createur_evenement',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le type d\'utilisateur est obligatoire']),
                ],
            ])
            // Champs pour Adherent
            ->add('poids', NumberType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 30,
                        'max' => 300,
                        'minMessage' => 'Le poids doit être d\'au moins {{ limit }} kg',
                        'maxMessage' => 'Le poids ne peut pas dépasser {{ limit }} kg',
                    ]),
                ],
            ])
            ->add('taille', NumberType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 100,
                        'max' => 250,
                        'minMessage' => 'La taille doit être d\'au moins {{ limit }} cm',
                        'maxMessage' => 'La taille ne peut pas dépasser {{ limit }} cm',
                    ]),
                ],
            ])
            ->add('age', NumberType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 12,
                        'max' => 120,
                        'minMessage' => 'L\'âge doit être d\'au moins {{ limit }} ans',
                        'maxMessage' => 'L\'âge ne peut pas dépasser {{ limit }} ans',
                    ]),
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                    'Autre' => 'autre',
                ],
            ])
            ->add('objectifPersonnel', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
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
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance',
                ],
            ])
            // Champs pour Coach
            ->add('anneeExperience', NumberType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 50,
                        'minMessage' => 'Le nombre d\'années d\'expérience doit être d\'au moins {{ limit }}',
                        'maxMessage' => 'Le nombre d\'années d\'expérience ne peut pas dépasser {{ limit }}',
                    ]),
                ],
            ])
            ->add('specialite', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => SpecialiteEnum::cases(),
                'choice_label' => function (SpecialiteEnum $specialite) {
                    return ucfirst($specialite->value);
                },
            ])
            ->add('cv', FileType::class, [
                'mapped' => false,
                'required' => false,
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
            ])
            // Champs pour CreateurEvenement
            ->add('nomOrganisation', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'organisation est obligatoire']),
                ],
            ])
            ->add('descriptionCreateur', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire']),
                ],
            ])
            ->add('adresseCreateur', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse est obligatoire']),
                ],
            ])
            ->add('telephoneCreateur', TelType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire']),
                    new Regex([
                        'pattern' => '/^[0-9]{8,}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide',
                    ]),
                ],
            ])
            // Champs pour InvestisseurProduit
            ->add('nomEntreprise', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire']),
                ],
            ])
            ->add('descriptionInvestisseur', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire']),
                ],
            ])
            ->add('adresseInvestisseur', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse est obligatoire']),
                ],
            ])
            ->add('telephoneInvestisseur', TelType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire']),
                    new Regex([
                        'pattern' => '/^[0-9]{8,}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_extra_fields' => true,
        ]);
    }
} 