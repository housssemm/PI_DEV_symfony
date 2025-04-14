<?php

namespace App\Form;

use App\Entity\Panierproduit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('quantite', IntegerType::class, [
                    'label' => 'Quantité :',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'data' => 1, // Valeur par défaut à 1
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Panierproduit::class,
        ]);
    }
}
