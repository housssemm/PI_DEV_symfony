<?php

namespace App\Form;

use App\Entity\Plats;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PlatsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du plat',
                'required' => true,
                'attr' => ['maxlength' => 30],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du plat',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG)',
                    ])
                ],
            ])
            ->add('ingredients', TextareaType::class, [
                'label' => 'Ingrédients',
                'required' => true,
                'attr' => ['maxlength' => 200],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['maxlength' => 500],
            ])
            ->add('nbCalories', IntegerType::class, [
                'label' => 'Nombre de calories',
                'required' => true,
            ])
            ->add('idNutritionist', IntegerType::class, [
                'label' => 'ID du nutritionniste',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Plats::class,
        ]);
    }
}

