<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;


class RType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champs communs
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre adresse email.']),
                    new Assert\Email(['message' => 'Veuillez saisir une adresse email valide.']),
                    new Assert\Length([
                        'max' => 180,
                        'maxMessage' => 'L\'adresse email ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre nom.']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'

                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÖØ-öø-ÿ\s\'-]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres, espaces ou tirets.'
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre prénom.']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÖØ-öø-ÿ\s\'-]+$/',
                        'message' => 'Le prénom ne doit contenir que des lettres, espaces ou tirets.'
                    ]),
                ],
            ])

            // Champs spécifiques à Adherent (sans style ici)
            ->add('poids', NumberType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('taille', NumberType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('age', IntegerType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('genre', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme'
                ],
                'placeholder' => 'Choisissez votre genre'
            ])
            ->add('objectifPersonnel', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Perdre_Poids' => 'Perdre_Poids',
                    'Avoir_des_muscles' => 'Avoir_des_muscles',
                    'Relaxation' => 'Relaxation'
                ],
                'placeholder' => 'Choisissez un objectif'
            ])
            ->add('niveauActivite', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Debutant' => 'Debutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Avancé' => 'Avancé'
                ],
                'placeholder' => 'Choisissez votre niveau'
            ])
            // Champs spécifiques à Coach
            ->add('anneeExperience', IntegerType::class, [
                'mapped' => false,
                'required' => false,
            ])

            ->add('specialite', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Musculation' => 'musculation',
                    'Yoga' => 'yoga',
                    'Gymnastique' => 'gymnastique',
                    'Pilates' => 'pilates',
                    'Danse' => 'danse'
                ],
                'placeholder' => 'Choisissez votre spécialité'
            ])
            ->add('cv', FileType::class, [
                'mapped' => false, // Le champ n'est pas mappé directement sur l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                    ])
                ]
            ])
            // Champs spécifiques à Créateur d'évènement
            ->add('nomOrganisation', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('descriptionCreateur', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('adresseCreateur', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('telephoneCreateur', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            // Champs spécifiques à Investisseur Produit
            ->add('nomEntreprise', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('descriptionInvestisseur', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('adresseInvestisseur', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('telephoneInvestisseur', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}


