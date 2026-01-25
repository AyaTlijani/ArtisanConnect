<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormError;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event Name',
                'attr' => ['placeholder' => 'Enter event name'],
                'help' => 'The name that will appear for the event',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter event description',
                    'rows' => 4,
                ],
            ])
            ->add('date', TextType::class, [
                'label' => 'Date',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'date',
                ],
                'help' => 'Select the date of the event',
            ])
            ->add('startTime', TextType::class, [
                'label' => 'Start Time',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'time',
                ],
                'help' => 'Select the start time of the event',
            ])
            ->add('endTime', TextType::class, [
                'required' => false,
                'label' => 'End Time',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'time',
                ],
                'help' => 'Select the end time of the event (optional)',
            ])
            ->add('capacity', NumberType::class, [
                'label' => 'Capacity',
                'attr' => ['min' => 1],
                'help' => 'Maximum number of participants',
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price',
                'attr' => ['min' => 0, 'step' => '0.01'],
                'help' => 'Price in DT (use 0 for free events)',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Upcoming' => 'Upcoming',
                    'Ongoing' => 'Ongoing',
                    'Completed' => 'Completed',
                    'Cancelled' => 'Cancelled',
                ],
                'placeholder' => 'Select a status',
                'required' => true,
                'data' => 'Upcoming', // Default status
            ])
            ->add('organizer', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Organizer',
                'placeholder' => 'Select an organizer',
            ])
            ->add('atelier', EntityType::class, [
                'class' => Atelier::class,
                'choice_label' => 'nom',
                'label' => 'Atelier',
                'required' => false,
                'placeholder' => 'Select an atelier (optional)',
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'adresse',
                'label' => 'Location',
                'required' => false,
                'placeholder' => 'Select a location (optional)',
            ]);

        // Transformers for time fields to ensure proper DateTime handling
        

        // Form event to validate endTime is after startTime
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            if ($data->getEndTime() !== null && $data->getStartTime() !== null) {
                if ($data->getEndTime() <= $data->getStartTime()) {
                    $form->get('endTime')->addError(new FormError('End time must be after start time.'));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}