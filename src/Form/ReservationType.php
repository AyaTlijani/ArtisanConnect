<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableQuantity = $options['available_quantity'] ?? 20;
        
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom complet',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre email']),
                    new Email(['message' => 'Veuillez entrer un email valide']),
                ],
            ])
            ->add('numeroTelephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de réservation',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une date']),
                ],
            ])
            ->add('heure', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure de réservation',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une heure']),
                ],
            ])
            ->add('nombrePersonnes', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'attr' => [
                    'min' => 1,
                    'max' => $availableQuantity,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le nombre de personnes']),
                    new GreaterThanOrEqual([
                        'value' => 1,
                        'message' => 'Le nombre de personnes doit être d\'au moins 1',
                    ]),
                    new LessThanOrEqual([
                        'value' => $availableQuantity,
                        'message' => 'Le nombre de personnes ne peut pas dépasser {{ compared_value }}',
                    ]),
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaires ou demandes spéciales',
                'required' => false,
                'attr' => ['rows' => 4],
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Le commentaire ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'available_quantity' => 20, // Default value if not provided
        ]);
        
        $resolver->setAllowedTypes('available_quantity', 'int');
    }
}
