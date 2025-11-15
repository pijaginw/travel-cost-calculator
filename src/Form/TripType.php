<?php

namespace App\Form;

use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // FR-005 & FR-006: A trip requires a name with a max length.
            // The validation constraints are defined in the Trip entity itself,
            // but we set the label and attributes here.
            ->add('tripName', TextType::class, [
                'label' => 'Trip Name',
                'attr' => [
                    'placeholder' => 'e.g., Summer in Italy',
                    'maxlength' => 70, // Corresponds to DB schema
                ],
            ])
            // FR-005: A trip requires a single currency.
            // CurrencyType provides a dropdown of ISO 4217 currency codes.
            ->add('tripCurrency', CurrencyType::class, [
                'label' => 'Trip Currency',
                'placeholder' => 'Choose a currency',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // This binds the form to the App\Entity\Trip class.
        // Symfony will use this to map form fields to entity properties.
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
