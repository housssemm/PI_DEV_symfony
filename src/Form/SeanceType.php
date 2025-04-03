<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\Coach;
use App\Entity\Planning;
use App\Entity\Seance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('Type')
            ->add('LienVideo')
            ->add('heureDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('heureFin', null, [
                'widget' => 'single_text',
            ])
            ->add('coach', EntityType::class, [
                'class' => Coach::class,
                'choice_label' => 'id',
            ])
            ->add('adherent', EntityType::class, [
                'class' => Adherent::class,
                'choice_label' => 'id',
            ])
            ->add('planning', EntityType::class, [
                'class' => Planning::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seance::class,
        ]);
    }
}
