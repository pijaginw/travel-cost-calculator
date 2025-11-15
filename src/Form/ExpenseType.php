<?php

namespace App\Form;

use App\Entity\Expense;
use App\Entity\ExpenseCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData of Expense
 *
 * @extends AbstractType<TData>
 */
class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Maps to the 'amount' property of the Expense entity.
            // Uses NumberType for decimal input.
            ->add('amount', NumberType::class, [
                'label' => 'Expense Amount',
                'scale' => 2, // Allows up to two decimal places
                'html5' => true,
                'attr' => [
                    'placeholder' => 'e.g., 45.99',
                    'min' => 0.01, // Enforces the Assert\Positive constraint
                ],
            ])
            // Maps to the 'category' property of the Expense entity.
            // Uses EnumType to list all predefined categories (FR-014).
            ->add('category', EnumType::class, [
                'class' => ExpenseCategory::class,
                'label' => 'Expense Category',
                'choice_label' => fn (ExpenseCategory $choice) => $choice->value,
                // The 'Uncategorized' default is handled in the Expense entity itself.
            ]);

        // Note: The 'trip' association is set programmatically in the controller,
        // so it does not appear in this form definition.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
            // Disable CSRF protection for the initial file-upload POST in the controller,
            // but keep it for the final save POST (the form submission).
            // In a real application, you'd manage this via separate forms or view logic.
        ]);
    }
}
