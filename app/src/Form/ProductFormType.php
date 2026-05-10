<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le titre est obligatoire'
                    ),
                    new Assert\Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Le titre doit faire au moins {{ limit }} caractères',
                        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères',
                    ),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'La description est obligatoire',
                    ),
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'scale' => 2, // nombre de décimales
                'currency' => 'EUR',
                'divisor' => 1,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Le prix est obligatoire',
                    ),
                    new Assert\Positive(
                        message: 'Le prix doit être positif',
                    ),
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'constraints' => [
                    new Assert\NotNull(
                        message: 'Veuillez choisir une catégorie',
                    ),
                ],
            ])
            ->add('images', FileType::class, [
                'label' => 'Images',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'constraints' => [
                    new Assert\All([
                        'constraints' => [
                            new Assert\File(
                                maxSize: '2M',
                                extensions: ['jpeg', 'jpg', 'png', 'webp'],
                            )
                        ]
                    ])                
                ],
                'attr' => ['accept' => 'image/jpeg,image/jpg, image/png,image/webp'] // validation front (HTML)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
