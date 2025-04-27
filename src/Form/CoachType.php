<?php

namespace App\Form;

use App\Entity\Coach;
use App\Enum\SpecialiteEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoachType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('anneesExperience', null, [
                'label' => 'Années d\'expérience',
                'required' => true,
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'required' => true,
                'choices' => SpecialiteEnum::cases(),
                'choice_label' => function (SpecialiteEnum $specialite) {
                    return ucfirst($specialite->value);
                },
            ])
            ->add('cv', FileType::class, [
                'label' => 'CV',
                'required' => true,
                'mapped' => false, // On ne mappe pas directement à la propriété cv (string)
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