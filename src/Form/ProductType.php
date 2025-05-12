<?php

namespace App\Form;
use App\Entity\Product;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom du livre',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Ex: Le Petit Prince'
                ]
            ])
            ->add('auteur', null, [
                'label' => 'Auteur',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’auteur est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom de l\'auteur doit contenir au moins {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Ex: Antoine de Saint-Exupéry'
                ]
            ])
            ->add('descreption', TextareaType::class, [ // Correction de 'descreption' à 'description'
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                    new Assert\Length([
                        'min' => 50,
                        'max' => 2000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez le livre en détail...'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (TND)',
                'scale' => 2,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire.']),
                    new Assert\PositiveOrZero(['message' => 'Le prix ne peut pas être négatif'])
                ],
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Ex: 29.99'
                ]
            ])
            ->add('ISBN', null, [
                'label' => 'ISBN',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'ISBN est obligatoire.']),
               
                ],
                'attr' => [
                    'placeholder' => 'Ex: 978-3-16-148410-0'
                ]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le stock est obligatoire.']),
                    new Assert\PositiveOrZero(['message' => 'Le stock ne peut pas être négatif'])
                ],
                'attr' => [
                    'min' => '0',
                    'placeholder' => 'Ex: 50'
                ]
            ])
            ->add('type_produit', ChoiceType::class, [
                'label' => 'Type de produit',
                'choices' => [
                    'À vendre' => 'à vendre',
                    'À emprunter' => 'à emprunter'
                ],
                'placeholder' => 'Choisissez un type',
             
                'attr' => ['class' => 'form-select']
            ])
            ->add('subCategories', EntityType::class, [
                'label' => 'Sous-catégories',
                'class' => SubCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Veuillez sélectionner au moins une sous-catégorie'
                    ])
                ],
                'attr' => [
                    'class' => 'select2',
                    'data-placeholder' => 'Sélectionnez les sous-catégories'
                ]
            ])
            ->add('image', FileType::class, [
            'label' => 'Image du livre',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG  PNG).',
                ])
            ],
        ]);
    }         
            

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'required_image' => true // Option personnalisée pour gérer l'obligation de l'image
        ]);
    }
}    