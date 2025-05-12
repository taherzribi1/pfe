<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class OrderType extends AbstractType
{

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('firstName', null, [
            'constraints' => [
                new NotBlank(['message' => 'Le nom est obligatoire']),
               
            ]
        ])
        ->add('lastName', null, [
            'constraints' => [
                new NotBlank(['message' => 'Le prénom est obligatoire']),
                
            ]
        ])
        ->add('phone', null, [
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Téléphone',
                'pattern' => '[0-9]{8}', // Validation HTML5
                'title' => 'Entrez 8 chiffres sans espaces ni caractères spéciaux'
            ],
            'constraints' => [
                new NotBlank(['message' => 'Le téléphone est obligatoire']),
                new NotBlank(['message' => 'Le numéro doit contenir uniquement 8 chiffres']),
               
            ]
        ])
        ->add('adresse', null, [
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new NotBlank(['message' => 'L\'adresse est obligatoire']),
                
            ]
        ])
        ->add('city', EntityType::class, [
            'class' => City::class,
            'choice_label' => 'name', // Déjà présent mais vérifiez l'orthographe
            'attr' => ['class' => 'form-control']
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}