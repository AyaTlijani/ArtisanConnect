<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Username',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a username']),
                    new Length(['max' => 8, 'maxMessage' => 'Your username should be at most {{ limit }} characters']),
                    new Regex(['pattern' => '/^[a-zA-Z0-9]+$/', 'message' => 'Username can only contain letters and numbers']),
                ],
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your full name']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your email']),
                ],
                'required' => true,
                'attr' => [
                    'autocomplete' => 'email',
                    'class' => 'form-control',
                ],
            ])
            ->add('accountType', ChoiceType::class, [
                'choices' => [
                    'Client' => 'CLIENT',
                    'Artisan' => 'ARTISAN',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Account Type',
                'mapped' => false, // This field is not mapped to a property
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select an account type']),
                ],
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your phone number']),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a password']),
                    new Length(['min' => 6, 'minMessage' => 'Your password should be at least {{ limit }} characters', 'max' => 4096]),
                ],
            ])
            ->add('location', HiddenType::class, [
                'label' => false,
                'mapped' => true,
                'attr' => [
                    'class' => 'location-field',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'signup_form',
        ]);
    }
    
    public function getBlockPrefix(): string
    {
        return ''; // This removes the form name prefix from field names
    }
}