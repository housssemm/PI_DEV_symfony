<?php

namespace App\Form;

use App\Entity\Offre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\OffreCoachType;
use App\Form\OffreProduitType;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('duree_validite', null, [
                'widget' => 'single_text',
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'ACTIVE',
                    'Expirée' => 'EXPIREE',
                ],
                'attr' => [
                    'class' => 'form-select bg-secondary text-white border-0'
                ],
                'label' => 'État',
            ])
            ->add('offrecoachs', CollectionType::class, [
                'entry_type' => OffreCoachType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Coachs associés',
            ])
            ->add('offreproduits', CollectionType::class, [
                'entry_type' => OffreProduitType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Produits associés',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}
