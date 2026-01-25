<?php

namespace App\Form;

use App\Service\PaymentService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodType extends AbstractType
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $paymentMethods = $this->paymentService->getAvailablePaymentMethods();
        $choices = [];
        
        foreach ($paymentMethods as $code => $method) {
            $choices[$method['name']] = $code;
        }

        $builder->add('paymentMethod', ChoiceType::class, [
            'label' => 'Select Payment Method',
            'choices' => $choices,
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'attr' => [
                'class' => 'payment-method-selector'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
