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

class R extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champs communs
            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
            ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('type', ChoiceType::class, [
                'mapped' => false,
                'choices'  => [
                    'Adhérent'              => 'adherent',
                    'Coach'                 => 'coach',
                    'Créateur d\'évènement'  => 'createur_evenement',
                    'Investisseur Produit'  => 'investisseur_produit'
                ],
                'placeholder' => 'Choisissez un type'
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
            ->add('genre', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('objectifPersonnel', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('niveauActivite', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            // Champs spécifiques à Coach
            ->add('anneeExperience', IntegerType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('Certificat_valide', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => 'Certificat valide ?'
            ])
            ->add('specialite', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('cv', TextType::class, [
                'mapped' => false,
                'required' => false,
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
            ->add('certificatValide', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => 'Certificat valide ?'
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
            ->add('certificatValideInvestisseur', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => 'Certificat valide ?'
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
