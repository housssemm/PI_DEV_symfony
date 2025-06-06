<?php

namespace App\Form;

use App\Entity\Coach;
use App\Entity\Offre;
use App\Entity\Offrecoach;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
class OffreCoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('nouveauTarif')
               ->add('nouveauTarif', NumberType::class, [
                     'required' => true,
                     'invalid_message' => 'Veuillez entrer un nombre valide.',
                 ])
            ->add('reservationActuelle')
            ->add('reservationMax')
            ->add('offre', EntityType::class, [
                'class' => Offre::class,
                'choice_label' => 'id',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offrecoach::class,
        ]);
    }
}
