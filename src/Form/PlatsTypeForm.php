<?php

namespace App\Form;

use App\Entity\Plats;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\File;

class PlatsFormType extends AbstractType // Renommé de PlatsType à PlatsFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id_nutritionist', IntegerType::class, [
                'label' => 'Nutritionist ID',
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'Name of the dish',
                'required' => true,
                'attr' => ['maxlength' => 30],
            ])
            ->add('image', FileType::class, [
                'label' => 'Dish Image',
                'mapped' => false, // Since you may store the path or the file itself
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
                        'mimeTypesMessage' => 'Please upload a valid image (PNG or JPG)',
                    ])
                ],
            ])
            ->add('ingrédients', TextareaType::class, [
                'label' => 'Ingredients',
                'required' => true,
                'attr' => ['maxlength' => 200],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['maxlength' => 500],
            ])
            ->add('nb_calories', IntegerType::class, [
                'label' => 'Number of Calories',
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


