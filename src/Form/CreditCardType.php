<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (int) date('Y');
        $years = range($currentYear, $currentYear + 10);
        $months = array_combine(
            array_map('str_pad', range(1, 12), array_fill(0, 12, 2), array_fill(0, 12, '0')),
            array_map('str_pad', range(1, 12), array_fill(0, 12, 2), array_fill(0, 12, '0'))
        );

        $builder
            ->add('card_number', TextType::class, [
                'label' => 'Card Number',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '1234 5678 9012 3456'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\CardScheme([
                        'schemes' => ['VISA', 'MASTERCARD', 'AMEX'],
                        'message' => 'Your credit card number is invalid.'
                    ])
                ]
            ])
            ->add('card_holder', TextType::class, [
                'label' => 'Card Holder Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'John Doe'
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('expiry_month', ChoiceType::class, [
                'label' => 'Expiry Month',
                'choices' => $months,
                'placeholder' => 'Month',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('expiry_year', ChoiceType::class, [
                'label' => 'Expiry Year',
                'choices' => array_combine($years, $years),
                'placeholder' => 'Year',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('cvv', TextType::class, [
                'label' => 'CVV',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '123'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 4,
                        'minMessage' => 'CVV should be {{ limit }} digits',
                        'maxMessage' => 'CVV should be {{ limit }} digits',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'CVV should contain only numbers'
                    ])
                ]
            ])
            ->add('payment_method', HiddenType::class, [
                'data' => 'credit_card',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'credit_card',
        ]);
    }
}
